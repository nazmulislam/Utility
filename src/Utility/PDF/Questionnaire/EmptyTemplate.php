
<?php
if(!isset($formData)) {
    $formData = json_decode($task->form_data);
}
$index = 1;
$elemtvalue = '';
if(empty($formDefaultLanguage)) 
{
    $formDefaultLanguage = isset($formData->user_data[0]->language)?$formData->user_data[0]->language:'' ;
}
foreach ($formData->user_data as $question) {
    $formLanguage = "english";
    $sectionNumber = isset($question->sectionIndex) ? $question->sectionIndex : '';
    $elementNumber = isset($question->elementIndex) ? $question->elementIndex : '';
    $sectiontitle =  isset($question->sectionTitle->$formDefaultLanguage) ? $question->sectionTitle->$formDefaultLanguage : (isset($question->sectionTitle->$formLanguage)?$question->sectionTitle->$formLanguage:$question->sectionTitle);

    $sectionNumaric = $sectionNumber + $index;
    $elementNumaric = $elementNumber + $index;

    if ($elementNumber === 0) {
        if ($sectionNumber != 0) {
            echo '<pagebreak />';
        }
        echo  '<h3>' . $sectionNumaric . '.' . $sectiontitle . '</h3>';
    }

    // echo '<div style="page-break-inside:avoid">';
    // echo "$sectionNumaric.$elementNumaric", '&nbsp;';
    // echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle . '</strong> <br>' : '';
    echo '</div>';
    echo '<div style="padding-bottom:25px;">
    
    </div>';
    
    if ($question->elementInputType =='h3') {
    // echo "<hr>";
        echo '<div style="page-break-inside:avoid">';
        echo "$sectionNumaric.$elementNumaric", '&nbsp;';
        if(isset($question->elementTitle->$formDefaultLanguage) && !empty($question->elementTitle->$formDefaultLanguage))
        {
            echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage)?$question->elementTitle->$formLanguage:$question->elementTitle);
        }
        else if(isset($question->sectionTitle->$formDefaultLanguage) && !empty($question->sectionTitle->$formDefaultLanguage))
        {
            echo isset($question->sectionTitle->$formDefaultLanguage) ? '<strong>' . $question->sectionTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->sectionTitle->$formLanguage)?$question->sectionTitle->$formLanguage:$question->sectionTitle);
        }
           // echo '<div style="padding-bottom:10px;"></div>';
    }


    $count = 0;
    $workflow = ["principal-contact", "address-personal", "trade-reference"];
    
    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage)?$question->elementTitle->$formLanguage:$question->elementTitle);
    echo '<div style="padding-bottom:10px;"></div>';

    echo (isset($elemtvalue)  ? $elemtvalue : '') . '<br><br>';
    echo '<hr>';
}
?>
