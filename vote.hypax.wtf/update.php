<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$msg = '';
// Check GET request for the poll ID (update.php?id=2, etc)
if (isset($_GET['id'])) {
    if (isset($_POST['title'])) {
        // This part is similar to the create.php, but instead we update a record and not insert
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $expires = isset($_POST['expires'], $_POST['expires_enabled']) && $_POST['expires_enabled'] ? $_POST['expires'] : NULL;
        $num_choices = isset($_POST['num_choices']) ? $_POST['num_choices'] : 1;
        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d\TH:i');
        $approved = approval_required ? 0 : 1;
        // Update the record
        $stmt = $pdo->prepare('UPDATE polls SET title = ?, description = ?, expires = ?, start_date = ?, approved = ?, num_choices = ? WHERE id = ?');
        $stmt->execute([ $title, $description, $expires, $start_date, $approved, $num_choices, $_GET['id'] ]);
        // New answers array
        $new_answers = [];
        // Iterate the post data and add the answers
        foreach($_POST as $k => $v) {
            // If the answer is empty, there is no need to insert
            if (strpos($k, 'answer') === false || empty($v)) continue;
            // Add answer to the "poll_answers" table
            $stmt = $pdo->prepare('INSERT IGNORE INTO poll_answers (poll_id, title) VALUES (?, ?)');
            $stmt->execute([ $_GET['id'], $v ]);
            // Add answer to array
            $new_answers[] = $v;
        }
        if ($new_answers) {
            // Delete the removed answers from the database
            $stmt = $pdo->prepare('DELETE FROM poll_answers WHERE poll_id = ? AND !FIND_IN_SET(title, ?)');
            $stmt->execute([ $_GET['id'], implode(',', $new_answers) ]);
        }
        // Output message
        if (!$approved) {
            $msg = 'Your poll is awaiting approval!';
        } else {
            $msg = 'Updated Successfully!';
        }
    }
    // Get the poll from the "polls" table by the get request ID
    $stmt = $pdo->prepare('SELECT * FROM polls WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    // Get the poll answers from the "poll_answers" table by the get request ID
    $stmt = $pdo->prepare('SELECT * FROM poll_answers WHERE poll_id = ?');
    $stmt->execute([ $_GET['id'] ]);
    // Fetch all the poll answers
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$poll) {
        exit('Poll doesn\'t exist with that ID!');
    }
} else {
    exit('No ID specified!');
}
?>
<?=template_header('Update Poll')?>

<div class="content update">

	<h2>Update Poll #<?=$poll['id']?></h2>

    <form action="" method="post">

        <label for="title">Title</label>
        <input type="text" name="title" value="<?=$poll['title']?>" id="title" placeholder="Title">

        <label for="description">Description</label>
        <input type="text" name="description" value="<?=$poll['description']?>" id="description" placeholder="Description">

        <label for="answers">Answers</label>
        <div class="answers">
            <?php for($i = 0; $i < count($answers); $i++): ?>
            <input type="text" name="answer_<?=$i+1?>" placeholder="Answer <?=$i+1?>" value="<?=$answers[$i]['title']?>">
            <?php endfor; ?>
        </div>
        <a href="#" class="add_answer"><i class="fas fa-plus"></i>Add Answer</a>

        <label for="start_date">Start Date</label>
        <input type="datetime-local" id="start_date" name="start_date" value="<?=$poll['start_date'] ? date('Y-m-d\TH:i', strtotime($poll['start_date'])) : date('Y-m-d\TH:i')?>">

        <label for="expires_enabled">Expires</label>
        <input type="checkbox" id="expires_enabled" name="expires_enabled"<?=$poll['expires'] ? ' checked' : ''?>>
        <input type="datetime-local" id="expires" name="expires" value="<?=$poll['expires'] ? date('Y-m-d\TH:i', strtotime($poll['expires'])) : date('Y-m-d\TH:i')?>" min="<?=date('Y-m-d\TH:i')?>">

        <label for="num_choices">Number of Choices</label>
        <input type="number" id="num_choices" name="num_choices" value="<?=$poll['num_choices']?>">

        <input type="submit" value="Update">

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
