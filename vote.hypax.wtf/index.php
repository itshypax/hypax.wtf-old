<?php
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();
// MySQL query that retrieves all the polls and poll answers
$stmt = $pdo->prepare('SELECT p.*, GROUP_CONCAT(pa.title ORDER BY pa.id) AS answers FROM polls p LEFT JOIN poll_answers pa ON pa.poll_id = p.id WHERE p.start_date < NOW() GROUP BY p.id');
$stmt->execute();
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_header('Polls')?>

<div class="content home">

	<h2>Polls</h2>

	<p>You can view all the list of available polls below.</p>

	<table>
        <thead>
            <tr>
                <td>#</td>
                <td>Title</td>
				<td class="responsive-hidden">Answers</td>
				<td class="responsive-hidden">Expires</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($polls as $poll): ?>
            <tr>
                <td><?=$poll['id']?></td>
                <td><?=$poll['title']?></td>
				<td class="responsive-hidden"><?=$poll['answers']?></td>
				<td class="responsive-hidden"><?=$poll['expires'] ? $poll['expires'] : 'Never'?></td>
                <td class="actions">
					<a href="vote.php?id=<?=$poll['id']?>" class="view" title="View Poll"><i class="fas fa-eye fa-xs"></i></a>
                    <!-- <a href="update.php?id=<?=$poll['id']?>" class="edit" title="Edit Poll"><i class="fas fa-pen fa-xs"></i></a> -->
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?=template_footer()?>
