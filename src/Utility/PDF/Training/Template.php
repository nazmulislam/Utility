
<?php
$formData = json_decode($task->form_data);
$index = 1;

$elemtvalue = '';
if(empty($formDefaultLanguage)) 
{
    $formDefaultLanguage = isset($formData->user_data[0]->language)?$formData->user_data[0]->language:'' ;
}
foreach ($formData->user_data as $question) {
    
    // echo '<div style="padding-bottom:25px;">
    
    // </div>';
    if ($question->elementInputType === "signature") {
            echo '<div style="padding-bottom:10px;"></div>';
             foreach ($question->elements as $element) {
                foreach ($element as $field) {
                    $elemtvalue = isset($field->postValue) ? $field->postValue : '';
                    $elemtvalue = str_replace('The box below is an e-signature field. Please use your mouse or touchpad to draw your signature inside the box.<br>', '', $elemtvalue);
                    $elemtname = isset($field->label) ? $field->label : '';

                    if ($field->key === "acceptTermsText" && $elemtname && $elemtvalue) {
                        echo (isset($elemtname) ? $elemtname : '') . '<br>';
                    } else if ($field->key === "informationText" && $elemtvalue) {
                        echo (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                    } else if ($field->key === "signatureValue" && ($elemtname || $elemtvalue)) {
                        $imagevalue = (string) $elemtvalue;
                        echo '<img src= "' . $imagevalue . '" />'. '<br>';
                    } else if ($field->key === "dateValue" && $elemtname && $elemtvalue) {
                        $datevalue = date('j F Y', strtotime($elemtvalue));
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($datevalue) ? $datevalue : '') . '<br>';
                    } else if (!empty($elemtvalue)) {
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                    }
            }
                echo '</div>';
            }
        }
}
?>
