@extends('layouts.app')

@push('scripts')
<script type="text/javascript">
	var previous = null;
	$(document).ready(function(){
		currentUser = '{{Auth::id()}}';
		$(document).on('focus', '.select-conv', function(){
			previous = $(this).val();
		}).on('change', '.select-conv', function(e){
			var ele = this;
			$('.error').css({'visibility': 'hidden'});
			var value = $(this).val();
			//If it is empty return to the previous value
			if(value === ''){
				$(this).val(previous);	
				//console.log('It had been before here=>' + previous);
			}else{
				if(jQuery.inArray(value, alreadySelected) < 0){
      				//Didn't exist in the array so we push it there
					alreadySelected.push(value);

      				//The number of the current flow(stage)
					var actualFlow = $(this).attr('name').substr(4,1);
					var name = 'flowu' + actualFlow;
					if(!$('select[name="'+ name + '"]').length){
      					//There is no siblings
						getDeptUsers(value, ele);
					}else{
						$('select[name="'+ name + '"]').next('button').remove();
						$('select[name="'+ name + '"]').remove();
						getDeptUsers(value, ele);
						removeElement(alreadySelected, previous);
						//console.log('That\s what we do we remove anything not working');
						//console.log('There is already a siblings');
					}
				}else if(jQuery.inArray(value, alreadySelected) >= 0 && jQuery.inArray(value, removedSelection) >= 0){
					console.log('Already Selected and already deleted');
					removeElement(removedSelection, value);
					//The number of the current flow(stage)
					var actualFlow = $(this).attr('name').substr(4,1);
					var name = 'flowu' + actualFlow;
					if(!$('select[name="'+ name + '"]').length){
      					//There is no siblings
						getDeptUsers(value, ele);
					}else{
						$('select[name="'+ name + '"]').next('button').remove();
						$('select[name="'+ name + '"]').remove();
						getDeptUsers(value, ele);
						removeElement(alreadySelected, previous);
						//console.log('That\s what we do we remmove anything not working');
						//console.log('There is already a siblings');
					}
				}else{ 
					var theSame = false;
					$('.select-conv.select-dept').each(function(){
						if($(this).val() === value && $(this) === ele){
      						//It is ours and it is us
							theSame = true;
						}
					});

					if(!theSame){
						alert('This department is already selected');
						$(this).val('');
					}
				} 
			}
		});

		//Handle form before submission
		$('.form.workflow').submit(function(e){
			console.log("We need to see data first after that you can go");
			$('<input>').attr('type', 'hidden').attr('name', 'removedFlow').attr('value', 
				JSON.stringify(removedFlow)).appendTo('#myForm');
			return true;
		});

		$('#customFile').on('change', function(e){
			var fileName = $(this).get(0).files[0].name;
			$(this).next('.custom-file-label').html(fileName);
			tmpUrl = URL.createObjectURL(this.files[0]);
			console.log("TmpUrl==>" + tmpUrl);
		});
	});

	function getDeptUsers(deptId, ele){
		var actualFlow = $(ele).attr('name').substr(4,1);
		var select = $('<select></select>');
		select.addClass('custom-select select-user col-md-5');
		//select.attr('required', 'required');
		var initOpt = $('<option></option>');
		initOpt.attr('value', '');
		initOpt.text('Choose a user');
		select.append(initOpt);
		//Remove Button
		var btn = $('<button></button>');
		btn.attr('type', 'button');
		btn.attr('onclick', 'removeReviewer("'+ $(ele).attr('name') +'")');
		btn.addClass('btn btn-danger btn-circle offset-md-1 col-md-1');
		var icon = $('<i></i>');
		icon.addClass('fas fa-times');
		btn.append(icon);

      	//Ajax query to get the users on this department
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			method:"POST",
			url: "{{route('deptUsers')}}",
			data: {id: deptId},
			success: function(data, status, xhr){
				for(var i=0; i < data.length; i++){
					var obj = data[i];
					//If object different than 
					//console.log("The Current User=>" + currentUser + " User=>" + obj.id);
					if(currentUser != obj.id && obj.id != 1){
						var option = $('<option></option>');
						option.attr('value', obj.id);
						option.text(obj.name);
						select.append(option);	
					}
				}
				select.attr('name', 'flowu' + actualFlow);
				$(ele).after(select); 
				$(select).after(btn);
				select.focus();
				$(select).closest('.form-group').attr('id', 'flow' + actualFlow);
			},
			error: function(xhr, status, error){
				console.log("XHR=>" + JSON.stringify(xhr));
				console.log("Status=>" + status);
				console.log("Error=>" + error);
			},
			dataType:"json"
		});
	}

</script>
@endpush

@push('styles')
<style type="text/css">
.document-container{
	height: 650px;
}

#view-exo{
	height: 500px;
	width: 100%;
	border: 2px solid #687179;
}

.error{
}
</style>
@endpush

@section('content')
<div class="container">
	<div class="stepwizard col-md-offset-3">
		<div class="stepwizard-row setup-panel">
			<div class="stepwizard-step">
				<a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
				<p>Document Metadata</p>
			</div>
			<div class="stepwizard-step">
				<a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
				<p>Document Workflow</p>
			</div>
			<div class="stepwizard-step">
				<a href="#step-3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</a>
				<p>Finish Settings</p>
			</div>
		</div>
	</div>

	<form role="form" action="{{route('documents.store')}}" method="post" 
	enctype="multipart/form-data" class="form workflow" id="myForm">
	{{ csrf_field() }}
	<input type="hidden" name="flow" id="flow" value="1">
	<div class="row setup-content" id="step-1">
		<div class="col-xs-6 col-md-offset-3">
			<div class="col-md-12">
				<div class="form-group">
					<div class="alert alert-danger error start-hidden"></div>
				</div>
				<h3> Document Metadata</h3>
				<div class="form-group">
					<label class="control-label">Document Location</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="customFile" 
						required="required" name="document" accept="application/pdf">
						<label class="custom-file-label" for="customFile">Choose file</label>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea required="required" class="form-control desca" placeholder="Document description" name="description" id="description"></textarea>
				</div>
				<button class="btn btn-primary nextBtn btn-lg pull-right" type="button">Next</button>
			</div>
		</div>
	</div>
	<div class="row setup-content" id="step-2">
		<div class="col-md-8">
			<div class="col-md-12 workflow-container">
				<div class="form-group">
					<div class="alert alert-danger error start-hidden"></div>
				</div>
				<h3> Document Workflow</h3>
				<div class="form-group">
					<label class="control-label">Add another reviewer</label>
					<button class="btn btn-success btn-circle add-reviewer" 
					onclick="addReviewer()" type="button"><i class="fa fa-plus"></i></button>
				</div>
				<div id="workflow-dept">
					<h4>Choose the reviewers that will review your document.</h4>
					<div class="form-group" id="flow1">
						<div class="">
							<select class="custom-select select-conv select-dept col-md-5" 
							name="flow1" >
							<option value="">Choose a department</option>
							@foreach($depts as $dept)
							<option value="{{$dept->id}}">{{ucfirst($dept->name)}}</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>
			<button class="btn btn-primary prevBtn btn-lg pull-left" type="button">Previous</button>
			<button class="btn btn-primary nextBtn btn-lg pull-right" type="button">Next</button>
		</div>
	</div>
</div>
<div class="row setup-content" id="step-3">
	<h3 class="d-flex justify-content-center"> Confirm Settings</h3>
	<div class="col-md-12">
		<!-- <div class="row document-container">
			<div class="offset-md-2 col-md-8">
				<canvas id="view-exo">
				</canvas>
			</div>
		</div>
		<div class="row">
			<div class="offset-md-2 col-md-8">
				<div class="card text-white bg-secondary mb-3" style="">
					<div class="card-header">
						Your description
					</div>
					<div class="card-body">
						<p class="card-text" id="desc-card">
						</p>
					</div>
				</div>
			</div>	
		</div>
		<div class="d-flex justify-content-center">
			<button class="btn btn-primary prevBtn btn-lg pull-left" type="button">Previous</button>
			<button class="btn btn-success btn-lg pull-right" type="submit">Confirm</button>
		</div> -->

		<div class="row">
			<div class="col-md-4">
				<canvas id="view-exo">
				</canvas>	
			</div>
			<div class="col-md-8">
				<div class="card text-white bg-secondary mb-3" style="">
					<div class="card-header">
						Your description
					</div>
					<div class="card-body">
						<p class="card-text" id="desc-card">
						</p>
					</div>
				</div>
				<div class="d-flex justify-content-center">
					<button class="btn btn-primary prevBtn btn-lg pull-left" type="button">Previous</button>
					<button class="btn btn-success btn-lg pull-right" type="submit">Confirm</button>
				</div>
			</div>
		</div>
	</div>
</div>
</form>
</div>
@endsection
