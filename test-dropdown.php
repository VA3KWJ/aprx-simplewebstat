<?php
$selectedTime = $_GET['time'] ?? '1';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dropdown Test</title>
</head>
<body>

<h2>Test Dropdown Auto-Submit</h2>

<form method="GET" action="test-dropdown.php">
    <label for="time">Select Time:</label>
    <select name="time" id="time" onchange="this.form.submit()">
        <option value="1" <?php if ($selectedTime == '1') echo 'selected'; ?>>Last Hour</option>
        <option value="2" <?php if ($selectedTime == '2') echo 'selected'; ?>>Last 2 Hours</option>
        <option value="4" <?php if ($selectedTime == '4') echo 'selected'; ?>>Last 4 Hours</option>
        <option value="e" <?php if ($selectedTime == 'e') echo 'selected'; ?>>All</option>
    </select>
</form>

<p>You selected: <strong><?= htmlspecialchars($selectedTime) ?></strong></p>

</body>
</html>
