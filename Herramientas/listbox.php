<html>
<head>
<title>List Box Form Data</title>
</head>
<body>
    <h3>List Box Form Data</h3>
    <p>Form data passed from the form</p>
    <?php
    echo "<p>select: " . $_POST['select']."</p>\n"; 
    echo "<p>listbox: " . $_POST['listbox'] . "</p>\n";
    $values = $_POST['listmultiple'];
    echo "<p>listmultiple: ";
    foreach ($values as $a){
            echo $a;}
    echo "</p>\n";
    ?>
</body>
</html>