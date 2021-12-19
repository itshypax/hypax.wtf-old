<?php
include 'main.php';
// Default poll values
$poll = [
    'title' => '',
    'description' => '',
    'expires' => date('Y-m-d\TH:i:s'),
    'submit_date' => date('Y-m-d\TH:i:s'),
    'start_date' => date('Y-m-d\TH:i:s'),
    'approved' => 0,
    'num_choices' => 1
];
$poll_answers = [];
if (isset($_GET['id'])) {
    // Retrieve the poll from the database
    $stmt = $pdo->prepare('SELECT * FROM polls WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    // Retrieve the poll answers from the database
    $stmt = $pdo->prepare('SELECT * FROM poll_answers WHERE poll_id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $poll_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // ID param exists, edit an existing poll
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Update the poll
        $stmt = $pdo->prepare('UPDATE polls SET title = ?, description = ?, expires = ?, submit_date = ?, start_date = ?, approved = ?, num_choices = ? WHERE id = ?');
        $expires = isset($_POST['expires_enabled']) ? date('Y-m-d H:i:s', strtotime($_POST['expires'])) : NULL;
        $stmt->execute([ $_POST['title'], $_POST['description'], $expires, date('Y-m-d H:i:s', strtotime($_POST['submit_date'])), date('Y-m-d H:i:s', strtotime($_POST['start_date'])), $_POST['approved'], $_POST['num_choices'], $_GET['id'] ]);
        // Get the answers and convert the multiline string to an array, so we can add each answer to the "poll_answers" table
        $answers = isset($_POST['answers']) ? explode(PHP_EOL, $_POST['answers']) : '';
        // New answers array
        $new_answers = [];
        // Iterate the answers array
        foreach ($answers as $answer) {
            // If the answer is empty, there is no need to insert
            if (empty($answer)) continue;
            // Add answer to the "poll_answers" table
            $stmt = $pdo->prepare('INSERT IGNORE INTO poll_answers (poll_id, title) VALUES (?, ?)');
            $stmt->execute([ $_GET['id'], $answer ]);
            // Add answer to array
            $new_answers[] = $answer;
        }
        if ($new_answers) {
            // Delete the removed answers from the database
            $stmt = $pdo->prepare('DELETE FROM poll_answers WHERE poll_id = ? AND !FIND_IN_SET(title, ?)');
            $stmt->execute([ $_GET['id'], implode(',', $new_answers) ]);
        }
        header('Location: polls.php');
        exit;
    }
    if (isset($_POST['delete'])) {
        // Delete the poll
        $stmt = $pdo->prepare('DELETE p, pa FROM polls p LEFT JOIN poll_answers pa ON pa.poll_id = p.id WHERE p.id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: polls.php');
        exit;
    }
} else {
    // Create a new poll
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO polls (title, description, expires, submit_date, start_date, approved, num_choices) VALUES (?,?,?,?,?,?,?)');
        $expires = isset($_POST['expires_enabled']) ? date('Y-m-d H:i:s', strtotime($_POST['expires'])) : NULL;
        $stmt->execute([ $_POST['title'], $_POST['description'], $expires, date('Y-m-d H:i:s', strtotime($_POST['submit_date'])), date('Y-m-d H:i:s', strtotime($_POST['start_date'])), $_POST['approved'], $_POST['num_choices'] ]);
        // Below will get the last insert ID, which is the poll id
        $poll_id = $pdo->lastInsertId();
        // Get the answers and convert the multiline string to an array, so we can add each answer to the "poll_answers" table
        $answers = isset($_POST['answers']) ? explode(PHP_EOL, $_POST['answers']) : '';
        foreach ($answers as $answer) {
            // If the answer is empty there is no need to insert
            if (empty($answer)) continue;
            // Add answer to the "poll_answers" table
            $stmt = $pdo->prepare('INSERT INTO poll_answers (poll_id, title) VALUES (?, ?)');
            $stmt->execute([ $poll_id, $answer ]);
        }
        header('Location: polls.php');
        exit;
    }
}
?>
<?=template_admin_header($page . ' Poll', 'polls')?>

<h2><?=$page?> Poll</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <label for="title">Title</label>
        <input id="title" type="text" name="title" placeholder="Title" value="<?=htmlspecialchars($poll['title'], ENT_QUOTES)?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Description"><?=htmlspecialchars($poll['description'], ENT_QUOTES)?></textarea>

        <label for="answers">Answers</label>
        <textarea id="answers" name="answers" placeholder="Answers"><?php foreach($poll_answers as $poll_answer): ?><?=htmlspecialchars($poll_answer['title'], ENT_QUOTES) . PHP_EOL?><?php endforeach; ?></textarea>

        <label for="expires_enabled">Expires?</label>
        <input id="expires_enabled" type="checkbox" name="expires_enabled" placeholder="Expires Enabled"<?=isset($poll['expires']) ? ' checked' : ''?>>
        <input id="expires" type="datetime-local" name="expires" value="<?=date('Y-m-d\TH:i:s', strtotime($poll['expires']))?>">

        <label for="submit_date">Date Submitted</label>
        <input id="submit_date" type="datetime-local" name="submit_date" value="<?=date('Y-m-d\TH:i:s', strtotime($poll['submit_date']))?>" required>

        <label for="start_date">Start Date</label>
        <input id="start_date" type="datetime-local" name="start_date" value="<?=date('Y-m-d\TH:i:s', strtotime($poll['start_date']))?>" required>

        <label for="num_choices">Number of Choices</label>
        <input id="num_choices" type="number" name="num_choices" placeholder="Number of Choices" value="<?=$poll['num_choices']?>" required>

        <label for="approved">Approved</label>
        <select id="approved" name="approved" required>
            <option value="0"<?=$poll['approved']==0?' selected':''?>>No</option>
            <option value="1"<?=$poll['approved']==1?' selected':''?>>Yes</option>
        </select>
        <br>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
            <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Delete" class="delete">
            <?php endif; ?>
        </div>

    </form>

</div>

<?=template_admin_footer()?>
