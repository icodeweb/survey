<?php 

include('config/database.php');
$title = 'Survey';
$submitted = false;

$select_questions_query = " SELECT *, 
												`question_options`.`id` as `option_id`, 
												`questions`.`id` as `questionId`
												FROM `questions` 
												LEFT JOIN `question_options` on `questions`.`id` = `question_options`.`question_id` 
												ORDER BY `questions`.`id` asc, `question_options`.`id` asc ";

$questions = mysqli_query($connection, $select_questions_query);

$questionsResult = $options = $types = [];
while($rows = mysqli_fetch_assoc($questions)) 
{
	$questionId = $rows['questionId'];
	$questionsResult[$questionId] = ['question' => $rows['question']];
	$types[$questionId] = $rows['input_type'];
	$options[$questionId][$rows['option_id']] = $rows['option'];
}


if(isset($_POST['btn_submit']))
{

	$userId = mysqli_real_escape_string($connection, trim($_POST['userId']));
	$full_name = mysqli_real_escape_string($connection, trim($_POST['full_name']));
	$contact_number = mysqli_real_escape_string($connection, trim($_POST['contact_number']));

	$insertUserQuery = " INSERT INTO `survey`.`users` (`userId`, `full_name`, `contact_number`) VALUES ('$userId', '$full_name', '$contact_number'); ";
	mysqli_query($connection, $insertUserQuery);
	$newUseId = mysqli_insert_id($connection);

	$answers = [];
	if(isset($_POST['answer']))
		$answers = $_POST['answer'];

	foreach ($answers as $questionId => $values)
	{
		$questionType = $types[$questionId];
		if($questionType == 1 || $questionType == 2)
		{
			$insert_query = " INSERT INTO `survey`.`answers` (`user_id`, `question_id`) VALUES ('$newUseId', '$questionId'); ";
			mysqli_query($connection, $insert_query);
			$answerId = mysqli_insert_id($connection);
			foreach ($values as $key => $value) 
			{
				$insertAnswerOptionQuery = " INSERT INTO `survey`.`answer_options` (`answer_id`, `option_id`) VALUES ('$answerId', '$value'); ";
				mysqli_query($connection, $insertAnswerOptionQuery);
			}
		} 
		else if($questionType == 3) 
		{
			$text_answer = $values['0'];
			$text_answer =  mysqli_real_escape_string($connection, $text_answer);
			$insert_query = " INSERT INTO `survey`.`answers` (`user_id`, `question_id`, `answer_text`) VALUES ('$newUseId', '$questionId', '$text_answer'); ";
			mysqli_query($connection, $insert_query);         
		}
	}
	$submitted = true;
}


?>

<?php include('includes/header.php'); ?>

<?php if($submitted) { ?>
<div class="alert alert-success mt-4">Survey completed successfully.</div>
<?php } ?>

	<form method="post">
		<div class="row">
			<div class="col-md-12">

				<div class="question"></div>
				<div class="question question-1 pb-4 mb-3">
						<div class="form-group row">
							<div class="col-sm-12">
								<input type="text" name="userId" class="form-control" placeholder="User ID">
							</div>
						</div>

						<div class="form-group row">
							<div class="col-sm-12">
								<input type="text" name="full_name" class="form-control" required value="" placeholder="Full Name*">
							</div>
						</div>


						<div class="form-group row">
							<div class="col-sm-12">
								<input type="text" name="contact_number" class="form-control" required value="" placeholder="Contact Number*">
							</div>
						</div>

					</div>

					
					<?php $counter = 1; foreach($questionsResult  as $questionId => $result) {?>
					<div class="question-1">
						<div class="form-group row mb-0">
							<div class="col-sm-12 col-form-label"><?php echo $counter; ?>. <?php echo htmlspecialchars($questionsResult[$questionId]['question']);?></div>
						</div>
						<div class="form-group row">
							<div class="col-sm-12">

							<?php if($types[$questionId] == '3') { ?>

								<div class="form-group">
									<input type="text" class="form-control" name="answer[<?php echo $questionId;?>][]">
								</div>

						  <?php } else { ?>    

							<?php $questionOptions = $options[$questionId]; ?>
							<?php foreach ($questionOptions as $optionId => $value) { ?>

								<div class="form-check form-check-inline">
									<?php if($types[$questionId] == '1') { ?>                           
										<input class="form-check-input" type="radio" name="answer[<?php echo $questionId;?>][]" value="<?php echo $optionId;?>">
									<?php } else if($types[$questionId] == '2') { ?>
										<input class="form-check-input" type="checkbox" name="answer[<?php echo $questionId;?>][]" value="<?php echo $optionId;?>">
									<?php } ?>
									<label class="form-check-label" for=""><?php echo htmlspecialchars($value);?></label>
								</div>

							<?php }?>

							<?php } ?>
							</div>
						</div>
					</div>
				<?php ++$counter; } ?>  

				<div class="mt-4">
					<button type="submit" name="btn_submit" class="btn btn-primary btn-sm float-left">Submit</button>
				</div>   

			</div>
		</div>
		</form>
	</div>
<?php include('includes/footer.php'); ?>