<form action="st.php" method="post">
<input type="text" name="date" size="255" />
</form>

<?php

echo strtotime('+' . $_POST['date']);

?>