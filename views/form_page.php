<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/trongate.css">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <h1>PHP 2 JS 2 HTML</h1>    
        <p class="text-center">Please in the your HTML code and then hit 'Submit'.</p>
        <?php
        echo form_open('php2js2html/submit');

        echo form_label('Function Name:');
        echo form_input('function_name', '', array('placeholder' => 'add a JavaScript function name in here...', 'autocomplete' => 'off'));

        echo form_label('New Element Name (for example \'resultsTable\'):');
        echo form_input('new_element_name', '', array('placeholder' => 'add a JavaScript variable name in here...', 'autocomplete' => 'off'));

        echo form_label('Parent Element ID');
        echo form_input('parent_id', '', array('placeholder' => 'add the parent element ID here...', 'autocomplete' => 'off'));

        $options[''] = 'Select...';
        $options[1] = 1;
        $options[2] = 2;
        $options[3] = 3;
        $options[4] = 4;

        echo form_label('Number of Spaces Per Indent:');
        echo form_dropdown('num_spaces_per_indent', $options, '');

        echo form_label('HTML code:');

        $attr['rows'] = 12;
        $attr['placeholder'] = 'Use this space to enter your HTML code...';
        echo form_textarea('html_code', '', $attr);
        echo '<p class="text-right">';
        echo form_button('clear', 'Clear Form', array('class' => 'alt', 'onclick' => 'clearForm()'));
        echo form_submit('submit', 'Submit');
        echo '</p>';
        echo form_close();
        ?>
    </div>

<style>
body {
    background-color: steelblue;
}

.container {
    background-color: #fff;
    min-height: 100vh;
}

h1 {
    text-align: center;
}
</style>

<script>
function clearForm() {
    const input1 = document.querySelector('body > div > form > input[type=text]:nth-child(2)');
    input1.value = '';

    const input2 = document.querySelector('body > div > form > input[type=text]:nth-child(4)');
    input2.value = '';

    const input3 = document.querySelector('body > div > form > input[type=text]:nth-child(6)');
    input3.value = '';

    const dropdown = document.querySelector('body > div > form > select');
    dropdown.selectedIndex = 0;

    const textarea = document.querySelector('body > div > form > textarea');
    textarea.value = '';
}
</script>
</body>
</html>