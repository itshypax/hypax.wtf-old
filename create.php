<?php
include 'functions.php';
// Connect to MySQL database
$pdo = pdo_connect_mysql();
$msg = '';
// Check if POST data exists
if (isset($_POST['title'])) {
    // Post data exists, insert a new record
    // Check all POST data variables
    $title = $_POST['title'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $expires = isset($_POST['expires'], $_POST['expires_enabled']) && $_POST['expires_enabled'] ? $_POST['expires'] : NULL;
    $num_choices = isset($_POST['num_choices']) ? $_POST['num_choices'] : 1;
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d\TH:i');
    $approved = approval_required ? 0 : 1;
    // Insert new record into the "polls" table
    $stmt = $pdo->prepare('INSERT INTO polls (title, description, expires, start_date, approved, num_choices) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([ $title, $description, $expires, $start_date, $approved, $num_choices ]);
    // Below will get the last insert ID, which will be the poll id
    $poll_id = $pdo->lastInsertId();
    // Iterate the post data and add the answers
    foreach($_POST as $k => $v) {
        // If the answer is empty, there is no need to insert
        if (strpos($k, 'answer') === false || empty($v)) continue;
        // Add answer to the "poll_answers" table
        $stmt = $pdo->prepare('INSERT INTO poll_answers (poll_id, title) VALUES (?, ?)');
        $stmt->execute([ $poll_id, $v ]);
    }
    // Output success message / approval message
    if (!$approved) {
        $msg = 'Your poll is awaiting approval!';
    } else {
        $msg = 'Created Successfully!';
    }
}
?>
<?=template_header('Create Poll')?>

<div class="content update">

	<h2>Create Poll</h2>

    <form action="" method="post">

        <label for="title">Title</label>
        <input type="text" name="title" id="title" placeholder="Title" required>

        <label for="description">Description</label>
        <input type="text" name="description" id="description" placeholder="Description">

        <label for="answers">Answers</label>
        <div class="answers">
            <input type="text" name="answer_1" placeholder="Answer 1" required>
        </div>
        <a href="#" class="add_answer"><i class="fas fa-plus"></i>Add Answer</a>

        <label for="start_date">Start Date</label>
        <input type="datetime-local" id="start_date" name="start_date" value="<?=date('Y-m-d\TH:i')?>">

        <label for="expires_enabled">Expires?</label>
        <input type="checkbox" id="expires_enabled" name="expires_enabled">
        <input type="datetime-local" id="expires" name="expires" value="<?=date('Y-m-d\TH:i')?>" min="<?=date('Y-m-d\TH:i')?>">

        <label for="num_choices">Number of Choices</label>
        <input type="number" id="num_choices" name="num_choices" value="1">

        <input type="submit" value="Create">

    </form>

    <?php if ($msg): ?>
    <p><?=$msg?></p>
    <?php endif; ?>

</div>

<script>
document.querySelector('.add_answer').onclick = function(event) {
    event.preventDefault();
    let num_answers = document.querySelector('.answers').childElementCount + 1;
    document.querySelector('.answers').insertAdjacentHTML('beforeend', '<input type="text" name="answer_' + num_answers + '" placeholder="Answer ' + num_answers + '">');
};
</script>

<?=template_footer()?>
