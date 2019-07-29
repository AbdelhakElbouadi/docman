<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Department;
use App\Document;
use App\DepartmentDocument;
use App\User;
use App\Review;
use App\Docversion;
use App\DocumentUser;
use App\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Get the sended documents for this user
        //Eliminate the document that this user is the actual author
        //DB::enableQueryLog();
        $finalCollection = null;
        $docs = DB::table('documents')->join('document_users', 'documents.id', '=',
           'document_users.doc_id')
        ->where('document_users.user_id', Auth::id())
        ->where(function($query){
            $query->where('documents.owner_id', '<>', Auth::id());
            $query->where('documents.status', 'NOT_REVIEWED');
        })->select('documents.id', 'documents.name', 'documents.path', 'documents.description', 
        'documents.version', 'documents.status', 'documents.owner_id', 
        'documents.created_at', 'documents.updated_at')->get();
        foreach ($docs as $key => $doc) {
            //Look for a confirmed review that this user made on this document 
            $reviewQuery = DB::table('reviews')->where('reviews.document_id', $doc->id)
            ->where(function($query){
                $query->where('reviews.user_id', Auth::id());
                $query->where('reviews.confirmed', true);
            })->get();
            if(count($reviewQuery)){
                $docs->forget($key);
            }     
        }
        //Choose the documents that the user has to review and he hadn't yet confirmed
        //dd(DB::getQueryLog());
        
        return view('home', ['docs'=>$docs]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $depts = Department::all();
        return view('document.create', ['depts'=>$depts]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $deptsId = array();
        $usersId = array();
        $file = $request->file('document');
        $name = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();
        
        $description = $request->description;
        $flow = $request->flow;
        $removedFlow = json_decode($request->removedFlow);

        $authorId =  Auth::id();
        $document = Document::create(['name'=>$name, 'description'=>$description, 
            'version'=>1, 'owner_id'=>$authorId, 'status'=>'NOT_REVIEWED']);
        //$path = 'uploads/'.basename($name, ".".$ext).$document->id.".".$ext;
        $path = $file->store('public/uploads');
        $path = substr($path, strpos($path, "uploads"));
        $document->path = $path;
        $res = $document->save();
        //Departments and users that should confirm this document
        for($i = 1; $i <= $flow; $i++){
            //If it exist in the removed flow we shouldn't use it at all
            if(!in_array($i, $removedFlow)){
                array_push($deptsId, $request->get("flow".$i));
                array_push($usersId, $request->get("flowu".$i));    
            }
        }

        //Notify all user that should validate this file
        foreach ($usersId as $id) {
            $userDoc = DocumentUser::create(['doc_id'=>$document->id, 'user_id'=>$id]);
            $note = Notification::create(['sender'=>$authorId, 'recipient'=>$id, 
                'doc_id'=>$document->id, 'version'=>$document->version, 'is_read'=>false,
                'datetime'=>date('Y-m-d H:i:s'), 
                'message'=>'Please review this document and approve it or mention to us in a comment what change we should implement']);
        }

        return redirect()->action("DocumentController@getMydocs");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $doc = Document::findOrFail($id);
        //Get review on this document
        $reviews = Review::where('document_id', $id)->orderBy('date', 'desc')->get();

        return view('document.view', ['doc'=>$doc, 'reviews'=>$reviews]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        echo "<br/>You want to update some data {$id}<br/>";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $doc = Document::findOrFail($id);  
        $retval = $doc->delete();
        $result = "failure";
        if($retval){
            $result = "success";
        }

        return $result;
    }

    public function deptsAjax(){
        $depts = Department::all();
        return response()->json($depts);      
    }

    public static function getAuthor($doc){
        $user = User::findOrFail($doc);
        return $user;
    }

    public static function formatDate($date, $pattern){
        $d = date_create($date);
        $format = date_format($d, $pattern);
        return $format;
    }

    public static function getReviewer($id){
        $reviwer_id = Review::findOrFail($id)->user_id;
        $reviewer = User::findOrFail($reviwer_id);

        return $reviewer;
    }

    public function getMydocs(){
        $docs = Document::where('owner_id', Auth::id())->get();

        return view('my-documents', ['docs'=>$docs]);
    }

    public function reupload(Request $request){
        $id = $request->documentId;
        $archived = new Docversion();
        $document = Document::findOrFail($id);
        $retval = "";
        if($request->hasFile('document')){
            //we should update the path and the version and store all its record on the version record
            $archived->doc_id = $id;
            $archived->path = $document->path;
            $archived->version = $document->version;
            $result = $archived->save();
            if($result){
                $file = $request->file('document');
                $path = $file->store('public/uploads');
                $path = substr($path, strpos($path, "uploads"));
                $document->path = $path;
                $document->version = strval(floatval($document->version) + 1);
                $res = $document->save();
                $retval = $path;
            }
        }
        //Send notifications to users to review this document again
        $toConfirmUsers = DB::table('users')->join('document_users', 'users.id', '=', 'document_users.user_id')
        ->where('document_users.doc_id', $id)->get();
        foreach ($toConfirmUsers as $user) {
            Notification::create(['sender'=>Auth::id(), 'recipient'=>$user->id, 
                'doc_id'=>$id, 'version'=>$document->version, 'is_read'=>false,
                'datetime'=>date('Y-m-d H:i:s'), 
                'message'=>'There is some new changes on this document as you recommanded. Please review that and see if it fits what you want']);    
        }

        return json_encode($retval);
    }

    public function documentHistory($id){
        //Looks for all its previous version with their metadata
        $document = Document::findOrFail($id);
        $prevVersions = Docversion::where('doc_id', $id)->get();
        //array_unshift($prevVersions, $document);

        return view('archives', ['first'=>$document, 'prev'=>$prevVersions]);        
    }

    public function loadVersion(Request $request){
        $versionId = $request->id;
        $version = Docversion::findOrFail($versionId);

        return json_encode($version->path);
    }

    public function deleteVersion(Request $request){
        $versionId = $request->id;
        $version = Docversion::findOrFail($versionId);
        $result = $version->delete();        
        if($result){
            $retval = "success";
            return json_encode($retval);
        }else{
            $retval = "failure";
            return json_encode($retval);
        }
    }

    public function deptUsersAjax(Request $request){
        $deptId = $request->id;
        $users = User::where('dept_id', $deptId)->get();

        return json_encode($users);
    }

    public function notify(Request $request){
        $userId = Auth::id();
        $notifications = Notification::where('recipient', $userId)
        ->where(function($query){
            $query->where('is_read', false);
        })->get();

        return view('notifications', ['notes'=>$notifications]);
    }

    public static function checkForNotification(){
        $userId = Auth::id();
        $notifications = Notification::where('recipient', $userId)
        ->where(function($query){
            $query->where('is_read', false);
        })->latest()->get();

        if(count($notifications)){
            return true;
        }

        return false;
    }

    public static function getDocumentName($id){
        $name = Document::findOrFail($id)->name;
        $name = substr($name, 0, strpos($name, "."));
        return $name;
    }

    public function markAsRead(Request $request){
        $noteId = $request->id;
        $note = Notification::findOrFail($noteId);
        $note->is_read = true;
        $note->save();

        return json_encode("success");
    }

    public static function getDept(){
        $depts = Department::all();
        return $depts;
    }
}
