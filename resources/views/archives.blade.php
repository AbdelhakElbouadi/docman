@extends('layouts.app')

@push('scripts')
<script type="text/javascript" src="{{asset('js/pdfobject.min.js')}}"></script>
<script type="text/javascript">

    function viewVersion(id){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            method: 'POST',
            url: "{{route('loadversion')}}",
            data: {id: id},
            success: function(data, status, xhr){
                var url = '/docman/public/storage/' + data;
                PDFObject.embed(url, "#version");    
            }, 
            error: function(xhr, status, error){
                console.log('XHR=>' + JSON.stringify(xhr));
                console.log('Status=>' + status);
                console.log('Error=>' + error);
            },
            dataType: 'json'
        });
    }

    function deleteVersion(id){
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
                    url: "{{route('deleteversion')}}",
                    data: {id: id},
                    success: function(data, status, xhr){
                        console.log('Data=>' + data);
                        console.log('Status=>' + status); 
                        if(data === "success"){
                            $('#' + id).remove();
                            Swal.fire(
                                'Deleted!',
                                'Your version has been deleted.',
                                'success'
                                );        
                        } 
                    }, 
                    error: function(xhr, status, error){
                        console.log('XHR=>' + JSON.stringify(xhr));
                        console.log('Status=>' + status);
                        console.log('Error=>' + error);
                    },
                    dataType: 'json'
                });
            }else{
                //$(this).prop('checked', false);
            }
        });
    }
</script>
@endpush

@push('styles')
<style>
.pdfobject-container { height: 30rem; border: 1rem solid rgba(0,0,0,.1); }

.document{
    height: 100%;
}

#view-doc{
    height: 100%;
}

@media (min-width: 576px){
    .modal-dialog{
        max-width: 80%;
        width: 100%;
        margin: 1.75rem auto;
    }
}

</style>
@endpush

@section('content')
@inject('documentController', 'App\Http\Controllers\DocumentController')
<div class="container">
    <div class="row">
        <h5>Your Documents</h5>
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th>Creation Date</th>
                    <th>Update Date</th>
                    <th>Version</th>
                    <th>Operations</th>    
                </tr>
            </thead>
            <tbody>
                <!--The first is the current version-->
                <tr>
                    <td>{{$documentController::formatDate($first->created_at, 'd/m/Y H:i:s')}}</td>
                    <td>{{$documentController::formatDate($first->updated_at, 'd/m/Y H:i:s')}}</td>
                    <td>{{$first->version}}</td>
                    <td>
                        <a href="{{route('documents.show', ['id'=>$first->id])}}">
                            <span class="bg-success text-danger">This is the current version</span>
                        </a>
                    </td>
                </tr>
                @if(count($prev))
                @foreach($prev as $doc)
                <tr class="bg-info" id="{{$doc->id}}">
                    <td>{{$documentController::formatDate($doc->created_at, 'd/m/Y H:i:s')}}</td>
                    <td>{{$documentController::formatDate($doc->updated_at, 'd/m/Y H:i:s')}}</td>
                    <td>{{$doc->version}}</td>
                    <td>
                        <button class="btn btn-primary" 
                        onclick="viewVersion('{{$doc->id}}')" data-toggle="modal" 
                        data-target="#view-doc">
                        <i class="fa fa-eye"></i>&nbsp;View</button>
                        <button class="btn btn-danger" onclick="deleteVersion('{{$doc->id}}')" 
                            ><i class="fa fa-trash"></i>&nbsp;Delete</button>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            <!--
                route('documents.show',['id'=>$doc->id])
            -->
        </table>
    </div>

    <div class="modal fade" id="view-doc" tabindex="-1" role="dialog" 
    aria-labelledby="myModalLabel" >
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">View Version</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="version">
                </div> 
            </div>
        </div>
    </div>
</div>
</div>
@endsection
