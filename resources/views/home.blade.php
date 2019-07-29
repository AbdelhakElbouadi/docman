@extends('layouts.app')

@section('content')
@inject('documentController', 'App\Http\Controllers\DocumentController')
<div class="container">
    <script type="text/javascript">
        $(document).ready(function(){
            currentUser = '{{Auth::id()}}';
            console.log("The Current User=>" + currentUser);    
        });
    </script>
    <div class="row">
        @if(count($docs))
        <h5>This Documents Need Your Approval</h5>
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Author</th>
                    <th>Creation Date</th>
                    <th>Version</th>
                    <th>Operations</th>    
                </tr>
            </thead>
            <tbody>
                @foreach($docs as $doc)
                <tr>
                    <td>{{substr($doc->name, 0, strpos($doc->name, "."))}}</td>
                    <td>{{$doc->description}}</td>
                    <td>{{$documentController::getAuthor($doc->owner_id)->name}}</td>
                    <td>{{$documentController::formatDate($doc->created_at, 'd/m/Y H:i:s')}}</td>
                    <td>{{$doc->version}}</td>
                    <td>
                        <a class="btn btn-primary" 
                        href="{{url('documents/'.$doc->id)}}" >
                            <i class="fa fa-eye"></i>&nbsp;View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <!--
                route('documents.show',['id'=>$doc->id])
            -->
        </table>
        @else
        <h5>There is no documents to approve yet</h5>
        @endif
    </div>
</div>
@endsection
