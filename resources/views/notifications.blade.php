@extends('layouts.app')

@push('scripts')
<script type="text/javascript">
	$(document).ready(function(){
		$('.switch1').change(function(e){
			var checked = false;
			var father = $(this).closest('.card');
			if($(this).is(':checked')){
				checked = true;
				var noteId = $(this).closest('form').find('input[name="noteId"]').val();
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					method: "POST",
					url: "{{route('mark')}}",
					data: {id: noteId},
					success: function(data, status, xhr){
						/*console.log("Data==>" + JSON.stringify(data));
						console.log("Status=>" + status);
						console.log("XHR==>" + JSON.stringify(xhr));*/	
						father.remove();
						if(!$(this).closest('.centered-container').has('.card')){
							$('.nocard').show();
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
				checked = false;
			}
			console.log('The change is made Value==>' + checked);
		});
	});
</script>
@endpush

@section('content')
@inject('documentController', 'App\Http\Controllers\DocumentController')
<div class="container">
	<script type="text/javascript">
		$(document).ready(function(){
			currentUser = '{{Auth::id()}}';   
		});
	</script>
	<div class="card notify-container">
		<div class="card-header">
			<h3 class="card-title">Notifications</h3>
		</div>
		<div class="card-body">
			<div class="centered-container">
				@if(count($notes))
				@foreach($notes as $note)
				<div class="row">
					<div class="card bg-default mb-3">
						<div class="card-header">
							<form class="form">
								<input type="hidden" name="noteId" value="{{$note->id}}">
								<div class="row">
									<p class="col-md-6">
										{{$documentController::getDocumentName($note->doc_id)}}
									</p>
									<div class="custom-control custom-switch col-md-3 ml-auto">
										<input type="checkbox" class="custom-control-input switch1" name="approval" id="note{{$note->id}}">
										<label class="custom-control-label" for="note{{$note->id}}">Mark as read</label>
									</div>	
								</div>	
							</form>
						</div>
						<div class="card-body">
							<h5 class="card-title">New Change Made</h5>
							<p class="card-text">
								{{$note->message}}
							</p>
							<p>
								<a href="{{route('documents.show', ['id'=>$note->doc_id])}}" 
									class="btn btn-default">Changed Document</a>
								</p>
								<div>
									<img src="{{asset('images/horn.png')}}" alt="horn" width="25px" 
									height="25px">&nbsp;<span>on 
										{{$documentController::formatDate($note->datetime, 'Y-m-d H:i:s')}}</span>
									</div>
								</div>
							</div>	
						</div>
						@endforeach
						@else
						<div class="alert alert-danger nocard" role="alert">
							There is no new notification to see.
						</div>
						@endif	
					</div>
				</div>
			</div>
		</div>
		@endsection
