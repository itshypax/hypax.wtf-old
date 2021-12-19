<?php
include 'main.php';
// Delete poll
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE p, pa FROM polls p LEFT JOIN poll_answers pa ON pa.poll_id = p.id WHERE p.id = ?');
    $stmt->execute([ $_GET['delete'] ]);
    header('Location: polls.php');
    exit;
}
// Approve poll
if (isset($_GET['approve'])) {
    $stmt = $pdo->prepare('UPDATE polls SET approved = 1 WHERE id = ?');
    $stmt->execute([ $_GET['approve'] ]);
    header('Location: polls.php');
    exit;
}
// SQL query that will retrieve all the polls from the database ordered by the submit_date column
$stmt = $pdo->prepare('SELECT p.*, GROUP_CONCAT(pa.title) AS answers FROM polls p LEFT JOIN poll_answers pa ON pa.poll_id = p.id GROUP BY p.id ORDER BY p.submit_date DESC');
$stmt->execute();
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_admin_header('Polls', 'polls')?>

<h2>Polls</h2>

<div class="links">
    <a href="poll.php">Create Poll</a>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td>Title</td>
                    <td class="responsive-hidden">Description</td>
                    <td>Answers</td>
                    <td>Approved</td>
                    <td>Expires</td>
                    <td class="responsive-hidden">Date</td>
                    <td>Actions</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($polls)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no recent polls</td>
                </tr>
                <?php else: ?>
                <?php foreach ($polls as $poll): ?>
                <tr>
                    <td><?=htmlspecialchars($poll['title'], ENT_QUOTES)?></td>
                    <td class="responsive-hidden"><?=nl2br(htmlspecialchars($poll['description'], ENT_QUOTES))?></td>
                    <td class="responsive-hidden"><?=htmlspecialchars($poll['answers'], ENT_QUOTES)?></td>
                    <td><?=$poll['approved']?'Yes':'No'?></td>
                    <td class="responsive-hidden"><?=$poll['expires'] ? date('F j, Y H:ia', strtotime($poll['expires'])) : 'Never'?></td>
                    <td class="responsive-hidden"><?=date('F j, Y H:ia', strtotime($poll['submit_date']))?></td>
                    <td>
                        <a href="../result.php?id=<?=$poll['id']?>" target="_blank">View</a>
                        <a href="poll.php?id=<?=$poll['id']?>">Edit</a>
                        <a href="polls.php?delete=<?=$poll['id']?>">Delete</a>
                        <?php if (!$poll['approved']): ?>
                        <a href="polls.php?approve=<?=$poll['id']?>">Approve</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>
