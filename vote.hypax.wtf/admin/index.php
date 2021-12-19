<?php
include 'main.php';
// Retrieve all polls submitted for the current day
$stmt = $pdo->prepare('SELECT p.*, GROUP_CONCAT(pa.title) AS answers FROM polls p LEFT JOIN poll_answers pa ON pa.poll_id = p.id WHERE cast(p.submit_date as DATE) = cast(now() as DATE) GROUP BY p.id ORDER BY p.submit_date DESC');
$stmt->execute();
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Retrieve the total number of polls that are awaiting approval
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM polls WHERE approved = 0');
$stmt->execute();
$awaiting_approval = $stmt->fetchColumn();
// Retrieve the total number of polls
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM polls');
$stmt->execute();
$polls_total = $stmt->fetchColumn();
// Retrieve the total number of polls
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM poll_answers');
$stmt->execute();
$poll_answers_total = $stmt->fetchColumn();
// Retrieve all the polls awaiting approval
$stmt = $pdo->prepare('SELECT p.*, GROUP_CONCAT(pa.title) AS answers FROM polls p LEFT JOIN poll_answers pa ON pa.poll_id = p.id WHERE p.approved = 0 GROUP BY p.id ORDER BY p.submit_date DESC');
$stmt->execute();
$polls_awaiting_approval = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_admin_header('Dashboard', 'dashboard')?>

<h2>Dashboard</h2>

<div class="dashboard">
    <div class="content-block stat">
        <div>
            <h3>Today's Polls</h3>
            <p><?=number_format(count($polls))?></p>
        </div>
        <i class="fas fa-poll-h"></i>
    </div>

    <div class="content-block stat">
        <div>
            <h3>Awaiting Approval</h3>
            <p><?=number_format($awaiting_approval)?></p>
        </div>
        <i class="fas fa-clock"></i>
    </div>

    <div class="content-block stat">
        <div>
            <h3>Total Polls</h3>
            <p><?=number_format($polls_total)?></p>
        </div>
        <i class="fas fa-poll-h"></i>
    </div>

    <div class="content-block stat">
        <div>
            <h3>Total Answers</h3>
            <p><?=number_format($poll_answers_total)?></p>
        </div>
        <i class="fas fa-list-ol"></i>
    </div>
</div>

<h2>Today's Polls</h2>

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

<h2 style="margin-top:40px">Awaiting Approval</h2>

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
                <?php if (empty($polls_awaiting_approval)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no polls awaiting approval</td>
                </tr>
                <?php else: ?>
                <?php foreach ($polls_awaiting_approval as $poll): ?>
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
