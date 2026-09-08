<article class ="application-card">

    <strong><?php echo htmlspecialchars($application['user_name']) ?></strong>


    <?php //UX: visar firstname + lastname för lite transparency då vi inte förhindrar att
          // en användare ansöker till samma grupp flera gånger, än.. ?>

    <p>
        <?php echo htmlspecialchars($application['first_name']) ?>
        <?php echo htmlspecialchars($application['last_name']) ?>
    </p>

    <form method ="POST" action="approveApplication.php">
        <input type = "hidden" name="application_id" value="<?php (int)$application['application_id']?>">
        <button type="submit">Accept</button>
    </form>

    <form method ="POST" action="declineApplication.php">
        <input type = "hidden" name="application_id" value="<?php (int)$application['application_id']?>">
        <button type="submit">Decline</button>
    </form>

</article>