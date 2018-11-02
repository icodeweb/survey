
<?php 

include('config/database.php');
$title = 'Survey form';
$submitted = false;

if(isset($_POST['btn_submit']))
{
	$questions = $questionTypes = $questionOptions = [];

	if(isset($_POST['question']))
		$questions = $_POST['question'];

	if(isset($_POST['type']))
		$questionTypes = $_POST['type'];

	if(isset($_POST['option']))
		$questionOptions = $_POST['option'];

	foreach($questions as $questionId => $question)
	{
		$questionType = $questionTypes[$questionId];
		$options = $questionOptions[$questionId];

		if($question)
		{
			$updateQuery = " UPDATE `survey`.`questions` SET `question` = '$question', `input_type` = '$questionType'  WHERE `id` = $questionId ";
			mysqli_query($connection, $updateQuery);
			foreach($options as $optionId => $option)
			{
				if($option)
				{
					$updteOptionQuery = " UPDATE `survey`.`question_options` SET `option` = '$option' WHERE id = '$optionId' ";
					mysqli_query($connection, $updteOptionQuery);
				}
			}
		}
		$submitted = true;
	}

	$questions = $questionTypes = $questionOptions = [];

	if(isset($_POST['new_question']))
	{

		$newQuestions = $_POST['new_question'];
		$newQuestionTypes = $_POST['new_type'];
		$newQuestionOptions = $_POST['new_option'];
		foreach($newQuestions as $questionId => $question)
		{
			$questionType = $newQuestionTypes[$questionId];         
		
			if($question)
			{
				$insertQuery = " INSERT INTO `survey`.`questions` (`question`, `input_type`) VALUES ('$question', '$questionType');";
				mysqli_query($connection, $insertQuery);
				if($questionType == 1 || $questionType == 2)
				{            
					$options = $newQuestionOptions[$questionId];
					$questionId = mysqli_insert_id($connection);
					foreach($options as $option)
					{
						if($option)
						{
							$insertOptionQuery = " INSERT INTO `survey`.`question_options` (`question_id`, `option`) VALUES ('$questionId', '$option'); ";
							mysqli_query($connection, $insertOptionQuery);
						}
					}
				}   
			}
			$submitted = true;
		}
	}
}


$select_query = " SELECT *, 
								`question_options`.`id` as `option_id`, 
								`questions`.id as questionId
								FROM `questions` 
								LEFT JOIN `question_options` on `questions`.`id` = `question_options`.`question_id` 
								ORDER BY `questions`.id asc, `question_options`.`id` asc ";

$questions = mysqli_query($connection, $select_query);

$questionsResult = [];
$types = [];
while($rows = mysqli_fetch_assoc($questions)) 
{
	$questionId = $rows['questionId'];
	$questionsResult[$questionId] = ['question' => $rows['question']];
	$types[$questionId] = $rows['input_type'];
	$options[$questionId][$rows['option_id']] = $rows['option'];
}

?>


<?php include('includes/header.php'); ?>

<?php if($submitted) { ?>
<div class="alert alert-success mt-4">Survey questions updated successfully.</div>
<?php } ?>

<?php if(isset($_GET['action']) && $_GET['action'] == 'deleted') { ?>
<div class="alert alert-danger mt-4">Survey question deleted successfully.</div>
<?php } ?>

	<form method="post">
		<div class="row">
			<div class="col-md-12">
					<div class="question question-0"></div>
					<?php $counter = 1; foreach($questionsResult  as $questionId => $result) {?>
					<div class="question question-1">
						<div class="form-group">
							<a href="remove.php?id=<?php echo $questionId;?>" onclick="return confirm('Do you really want to remove this question ?')" class="btn btn-danger btn-sm float-right">Delete</a>
							<h4><span class="badge badge-secondary">Question <?php echo $counter;?></span></h4>
						</div>
						<div class="form-group row">
							<label for="" class="col-sm-2 col-form-label">Question</label>
							<div class="col-sm-10">
								<input type="text" name="question[<?php echo $questionId;?>]" class="form-control" value="<?php echo $questionsResult[$questionId]['question'];?>" placeholder="Enter survey question">
							</div>
						</div>
						<div class="form-group row">
							<label for="staticEmail" class="col-sm-2 col-form-label">Type</label>
							<div class="col-sm-10 mt-2">
								<div class="form-check form-check-inline">
									<input class="form-check-input" onclick="showOptions(<?php echo $questionId;?>)" type="radio"<?php if($types[$questionId] == '1') echo 'checked'; ?> name="type[<?php echo $questionId;?>]" id="" value="1" checked>
									<label class="form-check-label" for="">Single</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" onclick="showOptions(<?php echo $questionId;?>)" type="radio"<?php if($types[$questionId] == '2') echo 'checked'; ?> name="type[<?php echo $questionId;?>]" id="" value="2">
									<label class="form-check-label" for="">Multiple</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" onclick="hideOptions(<?php echo $questionId;?>)" type="radio" <?php if($types[$questionId] == '3') echo 'checked'; ?> name="type[<?php echo $questionId;?>]" value="3">
									<label class="form-check-label" for="">Text</label>
								</div>
							</div>
						</div>
						<div class="form-group row options-list-<?php echo $questionId;?>" <?php if($types[$questionId] == '3') echo 'style="display:none"'; ?>>
							<label for="" class="col-sm-2 col-form-label">Options</label>
							<div class="col-sm-6">
							<?php $questionOptions = $options[$questionId]; ?>
							<?php foreach ($questionOptions as $optionId => $value) { ?>
										<input type="text" value="<?php echo $value;?>" name="option[<?php echo $questionId;?>][<?php echo $optionId;?>]" class="form-control mb-2 option-1" placeholder="Enter option value">
							<?php }?>
							</div>
						</div>
					</div>
				<?php ++$counter; } ?>
				<div class="mt-4">
					<button type="button" class="btn btn-success btn-sm float-right" onclick="addQuestion()">Add Question</button>
					<button type="submit" name="btn_submit" class="btn btn-primary btn-sm float-left">Submit</button>
				</div>            
			</div>
		</div>
		</form>
	</div>
	<footer class="my-5 pt-5 text-muted text-center text-small">
		<p class="mb-1"> footer </p>
		<ul class="list-inline">
		</ul>
	</footer>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script type="text/javascript">

		var questionId = '<?php echo $counter; ?>';
		function getQuestionFormElements(questionId)  {
			 var elements = '<div class="question question-'+questionId+'">' + 
						'<div class="form-group">' + 
							'<button type="button" onclick="removeQuestion('+questionId+')" class="btn btn-danger btn-sm float-right">Delete</button>' + 
							'<h4><span class="badge badge-secondary">Question '+questionId+'</span></h4>' + 
						'</div>' + 
						'<div class="form-group row">' + 
							'<label for="" class="col-sm-2 col-form-label">Question</label>' + 
							'<div class="col-sm-10">' + 
								'<input type="text" name="new_question['+questionId+']" class="form-control" id="" placeholder="Enter survey question">' + 
							'</div>' + 
						'</div>' + 
						'<div class="form-group row">' + 
							'<label for="staticEmail" class="col-sm-2 col-form-label">Type</label>' + 
							'<div class="col-sm-10 mt-2">' + 
								'<div class="form-check form-check-inline">' + 
									'<input onclick="showOptions('+questionId+')" class="form-check-input" type="radio" name="new_type['+questionId+']" id="" value="1" checked>' + 
									'<label class="form-check-label" for="">Single</label>' + 
								'</div>' + 
								'<div class="form-check form-check-inline">' + 
									'<input onclick="showOptions('+questionId+')" class="form-check-input" type="radio" name="new_type['+questionId+']" id="" value="2">' + 
									'<label class="form-check-label" for="">Multiple</label>' + 
								'</div>' + 
								'<div class="form-check form-check-inline">' + 
									'<input onclick="hideOptions('+questionId+')" class="form-check-input" type="radio" name="new_type['+questionId+']" id="" value="3">' + 
									'<label class="form-check-label" for="">Text</label>' + 
								'</div>' + 
							'</div>' + 
						'</div>' + 
						'<div class="form-group row options-list-'+questionId+'">' + 
							'<label for="" class="col-sm-2 col-form-label">Options</label>' + 
							'<div class="col-sm-6">' + 
								'<input type="text" name="new_option['+questionId+'][]" class="form-control mb-2 option-'+questionId+'" id="" placeholder="Enter option value">' + 
								'<input type="text" name="new_option['+questionId+'][]" class="form-control mb-2 option-'+questionId+'" id="" placeholder="Enter option value">' + 
								'<input type="text" name="new_option['+questionId+'][]" class="form-control mb-2 option-'+questionId+'" id="" placeholder="Enter option value">' + 
								'<a href="javascript:void(0)" onclick="addNewOption('+questionId+')">Add new option</a>' + 
							'</div>' + 
						'</div>' + 
					'</div>';
			return elements;
		}   

		function getOptionFormElements(questionId) {
			return newOptionElement = '<input type="text" name="new_option['+questionId+'][]" class="form-control mb-2 option-'+questionId+'" placeholder="Enter option value">';
		}

		function addQuestion(){
			var elements = getQuestionFormElements(questionId);
			$('.question:last').after(elements);
			++questionId;
		}

		
		function addNewOption(questionId){
			var newOptionElement = getOptionFormElements(questionId)
			$('.option-'+questionId+':last').after(newOptionElement);
		}


		function removeQuestion(questionId){
			if(confirm('Do you really want to remove this question ?')) {
				$('.question-'+questionId).remove();
			}
		}

		function hideOptions(questionId) {
			$('.options-list-'+questionId).hide();
		}


		function showOptions(questionId) {
			$('.options-list-'+questionId).show();
		}

	</script>
</body>
</html>