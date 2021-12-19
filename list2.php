<?php
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();
// MySQL query that selects all the polls and poll answers
$stmt = $pdo->prepare('SELECT p.*, GROUP_CONCAT(pa.title ORDER BY pa.id) AS answers, GROUP_CONCAT(pa.votes ORDER BY pa.id) AS answers_votes FROM polls p LEFT JOIN poll_answers pa ON pa.poll_id = p.id WHERE p.start_date < NOW() GROUP BY p.id');
$stmt->execute();
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_header('Polls Results')?>

<div class="content home">

	<h2>Polls Results</h2>

	<p>All results for all polls are below.</p>

	<div class="poll-list-2">
		<?php foreach($polls as $poll): ?>
		<div class="wrapper responsive-width-100">
			<h3 class="poll-title"><?=$poll['title']?></h3>
			<?php
			$answers = explode(',', $poll['answers']);
			$answers_votes = explode(',', $poll['answers_votes']);
			?>
			<?php for($i = 0; $i < count($answers); $i++): ?>
			<div class="poll-question">
				<p class="poll-txt"><?=$answers[$i]?> <span><?=$answers_votes[$i]?> Votes</span></p>
				<div class="result-bar-container">
					<div class="result-bar" style="width:<?=@(($answers_votes[$i]/array_sum($answers_votes))*100)?>%">
						<?=@round(($answers_votes[$i]/array_sum($answers_votes))*100)?>%
					</div>
				</div>
			</div>
			<?php endfor; ?>
		</div>
		<?php endforeach; ?>
	</div>

</div>

<?=template_footer()?>
