@extends('layouts.app')

@push('scripts')
<script type="text/javascript" src="{{asset('js/loadPdf.js')}}"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$('.form.reupload').submit(function(e){
			e.preventDefault();
			var formData = new FormData($(this)[0]);
			$.ajax({
				method: 'POST',
				url: "{{route('reupload')}}",
				data: formData,
				contentType: false,
				processData: false,
				success: function(data, status, xhr){
					var url = '/docman/public/storage/' + data;
					PDFObject.embed(url, "#canvas");	
				}, 
				error: function(xhr, status, error){
					console.log('XHR=>' + JSON.stringify(xhr));
					console.log('Status=>' + status);
					console.log('Error=>' + error);
				},
				dataType: 'json'
			});
		});

		$('#customFile').on('change', function(e){
			var fileName = $(this).get(0).files[0].name;
			$(this).next('.custom-file-label').html(fileName);
		});
	});
</script>
@endpush

@push('styles')
<style>
.pdfobject-container { height: 30rem; border: 1rem solid rgba(0,0,0,.1); }

.document{
	height: 100%;
}

.scrolla{
	height: 100%;
}

.review{
	overflow-y: scroll;
	height: 650px;
}

#comment{
	width: 100%;
}

#your-comment{
	margin-top: 15px;
}
</style>
@endpush

@section('content')
@inject('documentController', 'App\Http\Controllers\DocumentController')
<div class="container">
	<input type="hidden" name="docurl" id="docurl" value="{{asset('storage/'.$doc->path)}}">
	<div class="row">
		<div class="col-md-4 scrolla">
			<div class="review">
				<h2>Reviews on this document</h2>
				@if(count($reviews))
				@foreach($reviews as $review)
				<div class="card text-white mb-3 @if($review->confirmed) bg-success @else bg-secondary @endif" style="max-width: 18rem;">
					<div class="card-header">
						@if($review->confirmed) <i class="far fa-thumbs-up"></i>
						@else <i class="fas fa-thumbs-down"></i>  @endif
						Reviewed By {{ucfirst($documentController::getReviewer($review->id)->name)}} at 
						{{$documentController::formatDate($review->date, 'd/m/Y H:i:s')}}
					</div>
					<div class="card-body">
						<p class="card-text">
							{{$review->comment}}
						</p>
					</div>
				</div>
				@endforeach	
				@endif	
			</div>
		</div>
		<div class="col-md-8 document">
			<div id="canvas">
			</div>
			<!--if it is not the owner-->
			@if($doc->owner_id !== Auth::id())
			<div id="your-comment">
				<form class="form" method="POST" action="{{route('reviews.store')}}">
					@csrf
					<input type="hidden" name="documentId" value="{{$doc->id}}">
					<div class="form-group">
						<textarea id="comment" name="comment" rows="5" 
						placeholder="add your review on this document">
					</textarea>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="custom-control custom-switch col-md-4 offset-md-2">
							<input type="checkbox" class="custom-control-input" id="switch1" name="approval" >
							<label class="custom-control-label" for="switch1">Approve The Document</label>
						</div>
						<div class="col-md-6">
							<input type="submit" name="submit" value="Add Review" class="btn btn-primary float-right">
						</div>
					</div>
				</div>
			</form>	
		</div>
		<!--if it is the owner-->
		@else
		<div id="your-edit">
			<form class="form reupload" method="POST" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="documentId" value="{{$doc->id}}">
				<div class="form-group">
					<label class="control-label">Document Location</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="customFile" required="required" name="document">
						<label class="custom-file-label" for="customFile">Choose file</label>
					</div>
				</div>
				<div class="form-group">
					<input type="submit" class="btn btn-primary float-right" name="reupload" value="Reupload">
				</div>
			</form>
		</div>
		@endif
	</div>
</div>
</div>
@endsection
