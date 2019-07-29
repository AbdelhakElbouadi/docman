<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Review;
use App\Document;
use App\DepartmentDocument;
use App\DocumentUser;
use App\Notification;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $confirmed = false;
        $comment = $request->comment;
        $approval = $request->approval;
        $documentId = $request->documentId;
        $id = Auth::id();
        $date = date("Y-m-d H:i:s");
        if($approval === 'on'){
            $confirmed = true;
        }

        if($comment == null){
            $comment = "";
        }

        $review = Review::create(['user_id'=>$id, 'document_id'=>$documentId, 
            'comment'=>$comment, 'date'=>$date, 'confirmed'=>$confirmed]);
        //Find the people that already validated this document.
        $confirmedReviews = Review::where('document_id', $documentId)->where(
            function($query){$query->where('confirmed', true);})->get();
        //Find people that should validate this document.
        $userdoc = DocumentUser::where('doc_id', $documentId)->get();
        $doc = Document::findOrfail($documentId);
        if($confirmedReviews->count() === $userdoc->count()){
            $doc->status = "REVIEWED";
        }else{
            $doc->status = "NOT_REVIEWED";
        }
        $doc->save();

        $msg = "";
        if($confirmed){
            $msg = Auth::user()->name." has approved your document";
        }else{
            $msg = Auth::user()->name." mentioned some changes he want you to include on this document";
        }
        $note = Notification::create(['sender'=>Auth::id(), 'recipient'=>$doc->owner_id, 
            'doc_id'=>$doc->id, 'version'=>$doc->version, 'is_read'=>false,
            'datetime'=>date('Y-m-d H:i:s'), 
            'message'=>$msg]);

        if($confirmed){
            return redirect()->action('DocumentController@index');
        }

        return redirect()->action('DocumentController@show', ['id'=>$doc->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Hand it to me and i will take care of it
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
