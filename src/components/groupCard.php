<article class="group-card">

    <h3><?= htmlspecialchars($group['group_name']) ?></h3>

    <p><?= htmlspecialchars($group['group_description']) ?></p>

    <?php //Gör om group[id] till en int i URL'en?>
    <a href="/group.php?id=<?= (int)$group['id'] ?>">View group</a>

</article>