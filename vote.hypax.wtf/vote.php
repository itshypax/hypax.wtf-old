<?php
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();
// Output message
$msg = '';
// If the GET request "id" exists (poll id)...
if (isset($_GET['id'])) {
    // MySQL query that selects the poll records by the GET request "id"
    $stmt = $pdo->prepare('SELECT * FROM polls WHERE approved = 1 AND id = ?');
    $stmt->execute([ $_GET['id'] ]);
    // Fetch the record
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    // Check if the poll record exists with the id specified
    if ($poll) {
        // MySQL query that selects all the poll answers
        $stmt = $pdo->prepare('SELECT * FROM poll_answers WHERE poll_id = ?');
        $stmt->execute([ $_GET['id'] ]);
        // Fetch all the poll anwsers
        $poll_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // If the user clicked the "Vote" button...
        if (isset($_POST['poll_answer'])) {
            // Check if the poll has expired and the user has already voted
            if ($poll['expires'] && date('Y-m-d H:i:s') >= $poll['expires']) {
                // The poll expire datetime is less than the current server datetime
                $msg = 'This poll has expired! You can no longer vote!';
            } else if (isset($_COOKIE['poll' . $_GET['id']]) && one_vote_per_poll) {
                // User has already voted...
                $msg = 'You have already voted!';
            } else if (date('Y-m-d H:i:s') < $poll['start_date']) {
                // Poll has not yet started
                $msg = 'This poll has not yet started!';
            } else {
                // Update and increase the vote for the answer the user voted for
                if (is_array($_POST['poll_answer'])) {
                    foreach($_POST['poll_answer'] as $poll_answer) {
                        $stmt = $pdo->prepare('UPDATE poll_answers SET votes = votes + 1 WHERE id = ?');
                        $stmt->execute([ $poll_answer ]);
                    }
                } else {
                    $stmt = $pdo->prepare('UPDATE poll_answers SET votes = votes + 1 WHERE id = ?');
                    $stmt->execute([ $_POST['poll_answer'] ]);
                }
                // Set cookie to prevent user from voting multiple times on te same poll
                setcookie('poll' . $_GET['id'], true, time() + (10 * 365 * 24 * 60 * 60));
                // Redirect user to the result page
                header('Location: result.php?id=' . $_GET['id']);
                exit;
            }
        }
    } else {
        exit('Poll with that ID does not exist.');
    }
} else {
    exit('No poll ID specified.');
}
?>
<?=template_header($poll['title'])?>

<div class="content poll-vote">

	<h2><?=$poll['title']?></h2>

	<p><?=$poll['description']?></p>

    <form action="vote.php?id=<?=$_GET['id']?>" method="post">
        <?php for ($i = 0; $i < count($poll_answers); $i++): ?>
        <label>
            <input type="<?=$poll['num_choices'] > 1 ? 'checkbox' : 'radio'?>" name="poll_answer<?=$poll['num_choices'] > 1 ? '[]' : ''?>" value="<?=$poll_answers[$i]['id']?>">
            <?=$poll_answers[$i]['title']?>
        </label>
        <?php endfor; ?>
        <div>
            <input type="submit" value="Vote">
            <a href="result.php?id=<?=$poll['id']?>">View Result</a>
        </div>
    </form>

    <?php if ($msg): ?>
    <p><?=$msg?></p>
    <?php endif; ?>

</div>

<?php if ($poll['num_choices'] > 1): ?>
<script>
document.querySelectorAll('[name="poll_answer[]"]').forEach(function(element) {
    element.onchange = function(event) {
        if (document.querySelectorAll('[name="poll_answer[]"]:checked').length+1 > <?=$poll['num_choices']?>) {
            document.querySelectorAll('[name="poll_answer[]"]:not(:checked)').forEach(function(element) {
                element.disabled = true;
            });
        } else {
            document.querySelectorAll('[name="poll_answer[]"]').forEach(function(element) {
                element.disabled = false;
            });
        }
    };
});
</script>
<?php endif; ?>

<?=template_footer()?>
