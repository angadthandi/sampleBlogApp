<script type="text/javascript">
$(function(){
	/**
	 * ADD new comment
	**/
	$('#addCommentBtn').click(function(){
		var comment = $('#comment').val();
		var postId = '<?php echo $this->uri->segment(2); ?>';
		if(comment==''){
			alert('Please enter your comment!');
			$('#comment').focus();
			return false;
		} else {
			// add comment
			$.ajax({
				'url':'<?php echo $this->config->item('base_url').'createnewcomment'; ?>',
				'type':'POST',
				'dataType':'json',
				'data':{'postedComment':comment, 'postId':postId},
				error: function() {
					alert(errorMsg);
					return false;
				},
				success: function(data) {
					if(data.status == 1) {
						$('#comment').val('');
						$( "#commentList" ).prepend( data.commentHtml );
						$('html, body').animate({
							scrollTop: $("#commentList").offset().top
						}, 100);
						return false;
					} else {
						alert(errorMsg);
						return false;
					}
				}
			});
		}
	});
	
	/**
	 * DELETE comment
	**/
	$(document).on('click', '.deleteCommentClass', function() {
		var getCurrId = $(this).attr('id').split('_');
		var commentId = getCurrId[1];
		var process = confirm('Are you sure you want to delete this comment?');
		if(process){
			$.ajax({
				'url':'<?php echo $this->config->item('base_url').'deletecomment'; ?>',
				'type':'POST',
				'dataType':'json',
				'data':{'commentId':commentId},
				error: function() {
					alert(errorMsg);
					return false;
				},
				success: function(data) {
					if(data.status == 1) {
						$("#commentDiv_"+commentId).remove();
						return false;
					} else {
						alert(errorMsg);
						return false;
					}
				}
			});
		}
	});
	
	/**
	 * EDIT comment
	**/
	$(document).on('click', '.editCommentClass', function() {
		$('.commentClass').each(function(){
			$(".staticAreaClass").removeClass('hide');
			$(".dynamicAreaClass").addClass('hide');
		});
		
		var getCurrId = $(this).attr('id').split('_');
		var commentId = getCurrId[1];
		$("#commentHtml_"+commentId).addClass('hide');
		$("#editCommentText_"+commentId).removeClass('hide');
		
		var textAreaVal = $("#commentText_"+commentId).val();
		$("#commentText_"+commentId).focus();
		$("#commentText_"+commentId).val('');
		$("#commentText_"+commentId).val(textAreaVal);
	});
	
	/**
	 * SAVE edited comment
	**/
	$(document).on('click', '.saveCommentClass', function() {
		var getCurrId = $(this).attr('id').split('_');
		var commentId = getCurrId[1];
		var commentVal = $('#commentText_'+commentId).val();
		if(trim_all(commentVal)==''){
			alert('Please enter your comment!');
			$('#commentText_'+commentId).focus();
			return false;
		} else {
			// ajax call to update comment
			$.ajax({
				'url':'<?php echo $this->config->item('base_url').'editcomment'; ?>',
				'type':'POST',
				'dataType':'json',
				'data':{'commentId':commentId, 'commentVal':commentVal},
				error: function() {
					alert(errorMsg);
					return false;
				},
				success: function(data) {
					if(data.status == 1) {
						$("#commentDescription_"+commentId).html(data.updatedComment);
						$(".staticAreaClass").removeClass('hide');
						$(".dynamicAreaClass").addClass('hide');
						return false;
					} else {
						alert(errorMsg);
						return false;
					}
				}
			});
		}
	});
	
	/**
	 * CANCEL edit comment
	**/
	$(document).on('click', '.cancelCommentClass', function() {
		$(".staticAreaClass").removeClass('hide');
		$(".dynamicAreaClass").addClass('hide');
	});
	
	
	/**
	 * delete post
	**/
	$('#deletePost').click(function(){
		var processPost = confirm('Are you sure you want to delete this Post and all its related comments?');
		if(processPost){
			window.location.href = '<?php echo $this->config->item('base_url').'deletepost/'.$this->uri->segment(2); ?>';
		}
	});
});
</script>

<?php if(!empty($loggeduser)){ ?>
	<?php echo 'Hi '.ucfirst($loggeduser['name']).','; ?>
	<?php if($loggeduser['user_type_id']==1 && ($post['createdById']==$loggeduser['id'])){ ?>
		<p class="text-right">
			<a title="Edit Post" href="<?php echo $this->config->item('base_url').'editpost/'.$this->uri->segment(2); ?>"><span class="glyphicon glyphicon-edit"></span></a>
			<a title="Delete Post" id="deletePost" href="javascript:void(0);"><span class="glyphicon glyphicon-trash"></span></a>
		</p>
	<?php } ?>
	<hr />
<?php } ?>

<?php if ($notice = $this->session->flashdata('notification')):?>
<p class="notice"><?php echo $notice; ?></p>
<?php endif;?>

<h2><?php echo wordwrap($post['title'], 8, "\n", true); ?></h2>
<div class="list-group">
	<a href="javascript:void(0);" class="list-group-item">
		<p class="list-group-item-text"><?php echo wordwrap($post['description'], 8, "\n", true); ?></p>
		<p class="list-group-item-text">Posted By : <?php echo $post['createdByName']; ?></p>
		<p class="list-group-item-text">Date : <?php echo $post['date_created']; ?></p>
	</a>
</div>
<hr />

<h3>Comment(s)</h3>

<div class="row" id="commentList">
	<?php if(!empty($comments)){ ?>
		<?php foreach($comments as $val) { ?>
			<div class="col-sm-12 commentClass" id="commentDiv_<?php echo $val['id']; ?>">
				<a href="javascript:void(0);" class="list-group-item staticAreaClass" id="commentHtml_<?php echo $val['id']; ?>">
					<?php if(($post['createdById']==$loggeduser['id']) || ($val['commentedById']==$loggeduser['id'])){ ?>
						<div class="hover-btn">
							<button title="Delete Comment" type="button" class="close deleteCommentClass" data-dismiss="alert" id="deleteComment_<?php echo $val['id']; ?>">
								<span aria-hidden="true">×</span>
								<span class="sr-only">Close</span>
							</button>
						</div>
					<?php } ?>
					<p class="list-group-item-text" id="commentDescription_<?php echo $val['id']; ?>"><?php echo wordwrap($val['description'], 8, "\n", true); ?></p>
					<p class="list-group-item-text">Commented By : <?php echo $val['createdByName']; ?></p>
					<p class="list-group-item-text">Date : <?php echo $val['date_created']; ?></p>
					<button title="Edit Comment" type="button" class="btn btn-default btn-sm editCommentClass" id="editComment_<?php echo $val['id']; ?>">
					  <span class="glyphicon glyphicon-edit"></span> Edit Comment
					</button>
				</a>
				<a href="javascript:void(0);" class="list-group-item hide dynamicAreaClass" id="editCommentText_<?php echo $val['id']; ?>">
					<textarea class="form-control" id="commentText_<?php echo $val['id']; ?>"><?php echo $val['description']; ?></textarea>
					<button type="button" class="btn btn-default btn-sm saveCommentClass" id="editCommentBtn_<?php echo $val['id']; ?>">Save</button>
					<button type="button" class="btn btn-default btn-sm cancelCommentClass">Cancel</button>
				</a>
			</div>
		<?php } ?>
		<div class="col-sm-12">
			<?php echo $pager; ?>
		</div>
	<?php } ?>
</div>

<?php if(!empty($loggeduser)){ ?>
	<hr />
	<div class="form-group">
		<textarea class="form-control" placeholder="Comment..." name="comment" id="comment"></textarea>
	</div>
	<a class="btn btn-info btn-lg" href="javascript:void(0);" id="addCommentBtn" />Add Comment</a>
	<hr />
<?php } ?>