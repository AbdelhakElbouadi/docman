@extends('layouts.app')

@push('scripts')
<script type="text/javascript">
    function deleteDocument(id){
       Swal.fire({
        title: 'Are you sure?',
        text: "You cannot revert this back",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Delete it!'
    }).then((result) => {
        if (result.value) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                method: 'POST',
                url: "documents/" + id,
                data: {_method: "DELETE"},
                success: function(data, status, xhr){
                    if(data === "success"){
                        $('#' + id).remove();
                        Swal.fire(
                            'Deleted!',
                            'Your document has been deleted.',
                            'success'
                            );        
                    } 
                }, 
                error: function(xhr, status, error){
                    console.log('XHR=>' + JSON.stringify(xhr));
                    console.log('Status=>' + status);
                    console.log('Error=>' + error);
                }
            });
        }else{
        }
    });
}
</script>
@endpush

@section('content')
@inject('documentController', 'App\Http\Controllers\DocumentController')
<div class="container">
    <div class="row">
        @if(count($docs))
        <h5>Your Documents</h5>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr style="vertical-align: middle;">
                    <th style="width: 150px;">Name</th>
                    <th style="width: 150px;">Description</th>
                    <th>Author</th>
                    <th>Creation Date</th>
                    <th>Version</th>
                    <th>Status</th>
                    <th>Operations</th>    
                </tr>
            </thead>
            <tbody>
                @foreach($docs as $doc)
                <tr id="{{$doc->id}}" style="vertical-align: middle;">
                    <td style="width: 150px;">{{substr($doc->name, 0, strpos($doc->name, "."))}}</td>
                    <td style="width: 150px;">{{$doc->description}}</td>
                    <td>{{$documentController::getAuthor($doc->owner_id)->name}}</td>
                    <td>{{$documentController::formatDate($doc->created_at, 'd/m/Y H:i:s')}}</td>
                    <td>{{$doc->version}}</td>
                    <td class="@if($doc->status === 'NOT_REVIEWED') bg-warning @else bg-info @endif">{{$doc->status}}</td>
                    <td>
                        <a class="btn btn-primary" 
                        href="{{url('documents/'.$doc->id)}}" >
                        <i class="fa fa-eye"></i>&nbsp;View</a>
                        <a class="btn btn-info" href="{{url('archives/'.$doc->id)}}">
                            <i class="fas fa-folder"></i>&nbsp;Archives</a>
                        <button class="btn btn-danger" 
                            onclick="deleteDocument('{{$doc->id}}')" >
                            <i class="fa fa-trash"></i>&nbsp;Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            <!--
                route('documents.show',['id'=>$doc->id])
            -->
        </table>
        @else
        <h5>You have no document uploaded</h5>
        @endif
    </div>
</div>
@endsection
