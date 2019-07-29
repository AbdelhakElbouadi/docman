$(document).ready(function(){
	var canvas = $("#canvas");
	var url = $("#docurl").val();
	console.log('Url=>' + url);
	PDFObject.embed(url, "#canvas");

	$(document).on('change', '#switch1', function(){
		if(this.checked){
			Swal.fire({
				title: 'Are you sure?',
				text: "Once you approve it, it won't appear again on your dashboard",
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, Approve it!'
			}).then((result) => {
				if (result.value) {
					Swal.fire(
						'Approved!',
						'Your document has been approved.',
						'success'
						);
				}else{
					$(this).prop('checked', false);
				}
			});
		}
	});
});