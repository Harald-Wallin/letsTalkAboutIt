<article class="discussion-card">

    <h3><?= htmlspecialchars($discussion['discussion_name']) ?></h3>

    <p><?= htmlspecialchars($discussion['discussion_topic']) ?></p>

    <small>Started by<?= htmlspecialchars($discussion['user_name']) ?></small>

    <br><br>

    <a href="/discussion.php?id=<?= (int)$discussion['discussion_id'] ?>">
        View discussion
    </a>

</article>