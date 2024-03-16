<?php
declare(strict_types=1);
if (!isset($formData)) {
    $formData = json_decode($task->form_data);
}
$index = 1;
$elemtvalue = '';
if (empty($formDefaultLanguage)) {
    $formDefaultLanguage = isset($formData->user_data[0]->language) ? $formData->user_data[0]->language : '';
}
$sectionArr = [];
$headerNumbering = '';
foreach ($formData->user_data as $question) {
    $formLanguage = "english";
    $sectionNumber = isset($question->sectionIndex) ? $question->sectionIndex : '';
    $elementNumber = isset($question->elementIndex) ? $question->elementIndex : '';
    $sectiontitle = isset($question->sectionTitle->$formDefaultLanguage) ? $question->sectionTitle->$formDefaultLanguage : (isset($question->sectionTitle->$formLanguage) ? $question->sectionTitle->$formLanguage : $question->sectionTitle);
    if (isset($question->sectionTitle->$formDefaultLanguage) && !empty($question->sectionTitle->$formDefaultLanguage)) {
        $sectionArr[$question->sectionTitle->$formDefaultLanguage] = $question->sectionTitle->$formDefaultLanguage;
        $headerNumbering = count($sectionArr);
    }

    $sectionNumaric = $headerNumbering;
    $elementNumaric = $elementNumber + $index;

    if ($elementNumber === 0) {
        if ($sectionNumber != 0) {
            echo '<pagebreak />';
        }
        echo '<h3>' . $sectionNumaric . '.' . $sectiontitle . '</h3>';
    }
    echo '</div>';
    echo '<div style="padding-bottom:25px;">
    
    </div>';

    if ($question->elementInputType == 'h3') {
        echo '<div style="page-break-inside:avoid">';
        echo "$sectionNumaric.$elementNumaric", '&nbsp;';
        if (isset($question->elementTitle->$formDefaultLanguage) && !empty($question->elementTitle->$formDefaultLanguage)) {
            echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
        } else if (isset($question->sectionTitle->$formDefaultLanguage) && !empty($question->sectionTitle->$formDefaultLanguage)) {
            echo isset($question->sectionTitle->$formDefaultLanguage) ? '<strong>' . $question->sectionTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->sectionTitle->$formLanguage) ? $question->sectionTitle->$formLanguage : $question->sectionTitle);
        }
    }
    $count = 0;
    $workflow = ["principal-contact", "address-personal", "trade-reference"];
    if (isset($question->elements) && is_array($question->elements) && count($question->elements) > 0) {
        foreach ($question->elements as $element) {
            $count++;

            if ($question->elementInputType === "phone") {
                $code = isset($element->postValue->code) ? $element->postValue->code : '';
                $phoneno = isset($element->postValue->phone) ? $element->postValue->phone : '';

                $elemtvalue = $code . "-" . $phoneno;

                if (is_object($elemtvalue)) {
                    echo (isset($elemtvalue->data->signatureValue) ? $elemtvalue->data->signatureValue : '') . '<br>';
                    echo (isset($elemtvalue->data->nameValue) ? $elemtvalue->data->nameValue : '') . '<br>';
                    echo (isset($elemtvalue->data->dateValue) ? $elemtvalue->data->dateValue : '') . '<br>';
                } else {
                    echo '<div style="page-break-inside:avoid">';
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    echo '<div style="padding-bottom:10px;"></div>';

                    echo (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                    echo '<hr>';
                    echo '</div>';
                }
            }
            // this condition is for Workflow Fields 
            else if (in_array($question->elementInputType, $workflow)) {
                //echo "<hr>";
                if ($count == 1) {
                    echo '<div style="page-break-inside:avoid">';
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    if (isset($question->elementTitle->$formDefaultLanguage) && !empty($question->elementTitle->$formDefaultLanguage)) {
                        echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    } else if (isset($question->elementTitle) && !empty($question->elementTitle) && is_array($question->elementTitle)) {
                        echo ((isset($question->elementTitle->$formLanguage) && !empty($question->elementTitle->$formLanguage)) ? $question->elementTitle->$formLanguage : '');
                    } else {
                        echo isset($question->sectionTitle->$formDefaultLanguage) ? '<strong>' . $question->sectionTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->sectionTitle->$formLanguage) ? $question->sectionTitle->$formLanguage : '');
                    }
                    echo '<div style="padding-bottom:10px;"></div>';
                }
                // echo "<hr>";
                foreach ($element as $field) {
                    $elemtvalue = isset($field->postValue) ? $field->postValue : '';
                    $elemtname = isset($field->label) ? $field->label : '';

                    if (is_object($elemtvalue)) {
                        echo (isset($elemtvalue->data->signatureValue) ? $elemtvalue->data->signatureValue : '') . '<br>';
                        echo (isset($elemtvalue->data->nameValue) ? $elemtvalue->data->nameValue : '') . '<br>';
                        echo (isset($elemtvalue->data->dateValue) ? $elemtvalue->data->dateValue : '') . '<br>';
                    } else {
                        if ($elemtname && $elemtvalue) {
                            echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                        }
                    }
                }
                echo '<hr>';
                echo '</div>';
            }
            // this condition is for Workflow address-corporate Field
            else if ($question->elementInputType === "address-corporate") {
                echo "<hr>";
                $registeredLabel = isset($question->registeredLabel) ? $question->registeredLabel : '';
                $tradingLabel = isset($question->tradingLabel) ? $question->tradingLabel : '';
                if ($count == 1) {
                    echo '<div style="page-break-inside:avoid">';
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    echo '<div style="padding-bottom:10px;"></div>';
                }
                if (isset($element) && !empty($element)) {
                    foreach ($element as $field) {
                        $elemtvalue = isset($field->postValue) ? $field->postValue : '';
                        $elemtname = isset($field->label) ? $field->label : '';

                        if ($field->key === "regAddress" && $elemtname && $elemtvalue) {
                            echo '<strong>' . $registeredLabel . '</strong>';
                            echo '<br/>';
                            echo '<br/>';
                            echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                        } else if ($field->key === "address" && $elemtname && $elemtvalue) {
                            echo '<strong>' . $tradingLabel . '</strong>';
                            echo '<br/>';
                            echo '<br/>';
                            echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                        } else if ($elemtname && $elemtvalue) {
                            echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                        }
                    }

                    echo '<hr>';
                }
                echo '</div>';
            }
            // this condition is for Workflow personal-details Field 
            else if ($question->elementInputType === "personal-details") {
                echo "<hr>";

                echo '<div style="page-break-inside:avoid">';
                echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                echo '<div style="padding-bottom:10px;"></div>';
                foreach ($element as $field) {
                    $elemtvalue = isset($field->postValue) ? $field->postValue : '';
                    $elemtname = isset($field->label) ? $field->label : '';

                    if ($field->key === "title" && $elemtname && $elemtvalue) {
                        $titlevalue = $elemtvalue->text;
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($titlevalue) ? $titlevalue : '') . '<br>';
                    } else if ($field->key === "dateOfBirth" && $elemtname && $elemtvalue) {
                        $datevalue = date('j-m-Y', strtotime($elemtvalue));
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($datevalue) ? $datevalue : '') . '<br>';
                    } else if ($elemtname && $elemtvalue) {
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                    }
                }
                echo '<hr>';
                echo '</div>';
                echo '<div style="padding-bottom:10px;"></div>';
            }
            // this condition is for Workflow corporate-shareholders Field 
            else if ($question->elementInputType === "corporate-shareholders") {
                echo "<hr>";
                if ($count == 1) {
                    echo '<div style="page-break-inside:avoid">';
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    echo '<div style="padding-bottom:10px;"></div>';
                }

                foreach ($element as $field) {
                    $elemtvalue = isset($field->postValue) ? $field->postValue : '';
                    $elemtname = isset($field->label) ? $field->label : '';

                    if ($field->key === "counrty_registration" && $elemtname && $elemtvalue) {
                        $countryvalue = $elemtvalue->name;
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($countryvalue) ? $countryvalue : '') . '<br>';
                    } else if ($field->key === "date_of_registration" && $elemtname && $elemtvalue) {
                        $datevalue = date('j-m-Y', strtotime($elemtvalue));
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($datevalue) ? $datevalue : '') . '<br>';
                    } else if ($elemtname && $elemtvalue) {
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                    }
                }
                echo '<hr>';
                echo '</div>';
                echo '<div style="padding-bottom:10px;"></div>';
            }
            // this condition is for Workflow individual-shareholders Field
            else if ($question->elementInputType === "individual-shareholders") {
                if ($count == 1) {
                    echo '<div style="page-break-inside:avoid">';
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    if (isset($question->elementTitle->$formDefaultLanguage) && !empty($question->elementTitle->$formDefaultLanguage)) {
                        echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    } else if (isset($question->elementTitle) && !empty($question->elementTitle) && is_array($question->elementTitle)) {
                        echo ((isset($question->elementTitle->$formLanguage) && !empty($question->elementTitle->$formLanguage)) ? $question->elementTitle->$formLanguage : '');
                        //echo isset($question->elementTitle) ? $question->elementTitle : ((isset($question->elementTitle->$formLanguage) && !empty($question->elementTitle->$formLanguage))?$question->elementTitle->$formLanguage:$question->elementTitle);
                    } else if (isset($question->sectionTitle->$formDefaultLanguage) && !empty($question->sectionTitle->$formDefaultLanguage)) {
                        echo isset($question->sectionTitle->$formDefaultLanguage) ? '<strong>' . $question->sectionTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->sectionTitle->$formLanguage) ? $question->sectionTitle->$formLanguage : $question->sectionTitle);
                    }
                    echo '<div style="padding-bottom:10px;"></div>';
                }
                foreach ($element as $field) {
                    $elemtvalue = isset($field->postValue) ? $field->postValue : '';
                    $elemtname = isset($field->label) ? $field->label : '';

                    if ($field->key === "country_of_birth" && $elemtname && $elemtvalue) {
                        $countryvalue = $elemtvalue->name;
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($countryvalue) ? $countryvalue : '') . '<br>';
                    } else if ($field->key === "date_of_birth" && $elemtname && $elemtvalue) {
                        $datevalue = date('j-m-Y', strtotime($elemtvalue));
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($datevalue) ? $datevalue : '') . '<br>';
                    } else if ($elemtname && $elemtvalue) {
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                    }
                }
                echo '<hr>';
                echo '</div>';
                echo '<div style="padding-bottom:10px;"></div>';
            }
            // this condition is for Workflow company-officers Field
            else if ($question->elementInputType === "company-officers") {
                echo "<hr>";

                echo '<div style="page-break-inside:avoid">';
                echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                echo '<div style="padding-bottom:10px;"></div>';
                foreach ($element as $field) {
                    $elemtvalue = isset($field->postValue) ? $field->postValue : '';
                    $elemtname = isset($field->label) ? $field->label : '';
                    if ($field->key === "date_appointed" && $elemtname && $elemtvalue) {
                        $datevalue = date('j-m-Y', strtotime($elemtvalue));
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($datevalue) ? $datevalue : '') . '<br>';
                    } else if ($elemtname && $elemtvalue) {
                        echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                    }
                }
                echo '<hr>';
                echo '</div>';
            }
            // this condition is for Uplaod Fields
            else if ($question->elementInputType === "file") {
                $fileName = (isset($question->language) && !empty($question->language) && $question->language == 'hebrew') ? 'שם קובץ' : 'File Name';
                $fileSize = (isset($question->language) && !empty($question->language) && $question->language == 'hebrew') ? 'גודל קובץ (בתים)' : 'Size (bytes)';
                $fileType = (isset($question->language) && !empty($question->language) && $question->language == 'hebrew') ? 'סוג קובץ' : 'Type';
                echo '<div style="page-break-inside:avoid">';
                echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                echo '<div style="padding-bottom:10px;"></div>';
                echo '  <table style="width:600px; text-align: center;">
                        <thead>
                             <tr>	
                                <th>' . $fileName . '</th>	
                                <th>' . $fileSize . '</th>	
                                <th>' . $fileType . '</th>	
                            </tr>
                        </thead>';

                foreach ($element->postValue as $elemtvalue) {
                    $valuename = $elemtvalue->filename;
                    $valuesize = $elemtvalue->size;
                    $valuetype = $elemtvalue->type;
                    if (is_object($valuename)) {
                        echo (isset($valuename->data->signatureValue) ? $valuename->data->signatureValue : '') . '<br>';
                        echo (isset($valuename->data->nameValue) ? $valuename->data->nameValue : '') . '<br>';
                        echo (isset($valuename->data->dateValue) ? $valuename->data->dateValue : '') . '<br>';
                    } else {
                        echo '<tbody>
                                <tr>
                                    <td>' . $valuename . '</td>
                                    <td>' . $valuesize . '</td>
                                    <td>' . $valuetype . '</td>
                                </tr>
                            </tbody>';
                    }
                }
                echo '</table>';
                echo '<hr>';
                echo '</div>';
            }
            // this condition is for combo select Fields
            else if ($question->elementInputType === "comboselect") {
                $first = isset($element->postValue->comboSelectOne) ? $element->postValue->comboSelectOne : '';

                echo '<div style="page-break-inside:avoid">';
                echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                echo '<div style="padding-bottom:10px;"></div>';
                foreach ($element->postValue->comboSelectTwo as $second) {
                    $secondvalue = $second->label;

                    $elemtvalue = $first . " : " . $secondvalue;

                    if (is_object($elemtvalue)) {
                        echo (isset($elemtvalue->data->signatureValue) ? $elemtvalue->data->signatureValue : '') . '<br>';
                        echo (isset($elemtvalue->data->nameValue) ? $elemtvalue->data->nameValue : '') . '<br>';
                        echo (isset($elemtvalue->data->dateValue) ? $elemtvalue->data->dateValue : '') . '<br>';
                    } else {
                        echo (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                    }
                }
                echo '<hr>';
                echo '</div>';
            }
            // this condition is for signature 
            else if ($question->elementInputType === "signature") {
                // this foreach looping array when it is greater then 1
                echo '<div style="page-break-inside:avoid">';
                if (isset($question->disableTitle) && $question->disableTitle == 0) {
                    
                }
                echo '<div style="padding-bottom:10px;"></div>';
                foreach ($element as $field) {
                    $elemtvalue = isset($field->postValue) ? $field->postValue : '';
                    $elemtvalue = str_replace('The box below is an e-signature field. Please use your mouse or touchpad to draw your signature inside the box.<br>', '', $elemtvalue);
                    $elemtname = isset($field->label) ? $field->label : '';

                    if (is_object($elemtvalue)) {
                        echo (isset($elemtvalue->data->signatureValue) ? $elemtvalue->data->signatureValue : '') . '<br>';
                        echo (isset($elemtvalue->data->nameValue) ? $elemtvalue->data->nameValue : '') . '<br>';
                        echo (isset($elemtvalue->data->dateValue) ? $elemtvalue->data->dateValue : '') . '<br>';
                    } else {
                        if ($field->key === "acceptTermsText" && $elemtname && $elemtvalue) {
                            echo (isset($elemtname) ? $elemtname : '') . '<br>';
                        } else if ($field->key === "informationText" && $elemtvalue) {
                            echo (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                        } else if ($field->key === "signatureValue" && ($elemtname || $elemtvalue)) {
                            $imagevalue = (string) $elemtvalue;
                            echo '<img src= "' . $imagevalue . '" />';
                        } else if ($field->key === "dateValue" && $elemtname && $elemtvalue) {
                            $datevalue = date('j F Y', strtotime($elemtvalue));
                            echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($datevalue) ? $datevalue : '') . '<br>';
                        } else if (!empty($elemtvalue)) {
                            echo (isset($elemtname) ? $elemtname : '') . " : " . (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                        }
                    }
                }
                echo '<hr>';
                echo '</div>';
            } else if ($question->elementInputType === "select") {
                //  this conditon creating loop for select field's postvalue

                echo '<div style="page-break-inside:avoid">';
                if (isset($element->key) && $element->key === "select") {
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    echo '<div style="padding-bottom:10px;"></div>';
                    foreach ($element->postValue as $elemtvalue) {
                        $value = $elemtvalue;
                        echo (isset($value) ? $value : '') . '<br>';
                    }
                } else if (isset($element->key) && $element->key === "input") {
                    echo (isset($element->postValue) ? $element->postValue : '') . '<br>';
                }
                echo '<hr>';
                echo '</div>';
            } else if (isset($question->elementInputType) && $question->elementInputType === "checkbox") {
                //  this conditon creating loop for checkbox field's postvalue
                echo '<div style="page-break-inside:avoid">';
                if ($element->key === "checkbox") {
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    echo '<div style="padding-bottom:10px;"></div>';
                    foreach ($element->postValue as $elemtvalue) {
                        $value = $elemtvalue;

                        echo (isset($value) ? $value : '') . '<br>';
                    }
                } else if ($element->key === "input") {
                    echo (isset($element->postValue) ? $element->postValue : '') . '<br>';
                }
                echo '<hr>';
                echo '</div>';
            } else if (isset($question->elementInputType) && $question->elementInputType === "downloadPDF") {
                //  this conditon creating loop for checkbox field's postvalue
                echo '<div style="page-break-inside:avoid">';
                if ($element->key === "tickbox") {
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    echo '<div style="padding-bottom:10px;"></div>';
                    if (isset($element->postValue) && intval($element->postValue) == 1) {
                        echo '<input type="checkbox" checked="checked">';
                    } else {
                        echo '<input type="checkbox">';
                    }
                } else if ($element->key === "tickboxLabel") {
                    echo (isset($element->postValue) ? $element->postValue : '') . '<br>';
                }
                echo '</div>';
            } else if ($question->elementInputType === "countries") {
                //  this conditon creating loop for countries postvalue 
                echo '<div style="page-break-inside:avoid">';
                echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                echo '<div style="padding-bottom:10px;"></div>';
                foreach ($element->postValue as $elemtvalue) {
                    $value = $elemtvalue->name;
                    if (is_object($value)) {
                        echo (isset($value->data->signatureValue) ? $value->data->signatureValue : '') . '<br>';
                        echo (isset($value->data->nameValue) ? $value->data->nameValue : '') . '<br>';
                        echo (isset($value->data->dateValue) ? $value->data->dateValue : '') . '<br>';
                    } else {
                        echo (isset($value) ? $value : '') . '<br>';
                    }
                }
                echo '<hr>';
                echo '</div>';
            } else if ($question->elementInputType === "cpi-countries") {
                //  this conditon creating loop for select field's postvalue
                echo '<div style="page-break-inside:avoid">';
                echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                echo '<div style="padding-bottom:10px;"></div>';
                $cpiCountry = [];
                foreach ($element->postValue as $postValue) {
                    $value = $postValue->country_name;
                    $cpiCountry[] = (isset($value) ? $value : '');
                }
                echo implode(', ', $cpiCountry);
                echo '</div>';

                echo '</div>';
            }
            // this condition work for date input field
            else if ($question->elementInputType === "date") {
                echo '<div style="page-break-inside:avoid">';
                echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                echo '<div style="padding-bottom:10px;"></div>';
                $elemtvalue = isset($element->postValue) ? $element->postValue : '';
                $datevalue = date('j-m-Y', strtotime($elemtvalue));
                echo (isset($datevalue) ? $datevalue : '') . '<br>';
                echo '<hr>';
                echo '</div>';
            }
            // this condition work for radio input field
            else if ($question->elementInputType === "radio") {
                if (isset($element->valueForPDF) && !empty($element->valueForPDF))
                    $elemtvalue = isset($element->valueForPDF) ? $element->valueForPDF : '';
                else
                    $elemtvalue = isset($element->postValue) ? $element->postValue : '';

                echo '<div style="page-break-inside:avoid">';
                if (isset($element->key) && $element->key === "radio") {
                    echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
                    echo '<div style="padding-bottom:10px;"></div>';
                    echo (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                } else if (isset($element->key) && $element->key === "progressive") {
                    echo isset($element->postNameProgressive->$formDefaultLanguage) ? '<strong>' . $element->postNameProgressive->$formDefaultLanguage . '</strong> <br>' : (isset($element->postNameProgressive->$formDefaultLanguage) ? $element->postNameProgressive->$formDefaultLanguage : '');
                    echo '<div style="padding-bottom:10px;"></div>';
                    echo (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                }
                echo '<hr>';
                echo '</div>';
            }
            // this condition works when array is = 1
            else {
                $elemtvalue = isset($element->postValue) ? $element->postValue : '';
                echo '<div style="page-break-inside:avoid">';
                echo "$sectionNumaric.$elementNumaric", '&nbsp;';
                if (isset($question->elementTitle->$formDefaultLanguage) && !empty($question->elementTitle->$formDefaultLanguage)) {
                    echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : '');
                } else if (isset($question->elementTitle->$formLanguage) && !empty($question->elementTitle->$formLanguage)) {
                    echo isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : '';
                } else if (isset($question->elementTitle) && !empty($question->elementTitle)) {
                    echo isset($question->elementTitle) ? $question->elementTitle : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : '');
                } else {
                    echo isset($question->sectionTitle->$formDefaultLanguage) ? '<strong>' . $question->sectionTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->sectionTitle->$formLanguage) ? $question->sectionTitle->$formLanguage : '');
                }
                echo '<div style="padding-bottom:10px;"></div>';

                if (is_object($elemtvalue)) {
                    echo (isset($elemtvalue->data->signatureValue) ? $elemtvalue->data->signatureValue : '') . '<br>';
                    echo (isset($elemtvalue->data->nameValue) ? $elemtvalue->data->nameValue : '') . '<br>';
                    echo (isset($elemtvalue->data->dateValue) ? $elemtvalue->data->dateValue : '') . '<br>';
                } else if (is_array($elemtvalue)) {
                    //echo (isset($elemtvalue)  ? implode(',',$elemtvalue) : '') . '<br>';
                } else {
                    echo (isset($elemtvalue) ? $elemtvalue : '') . '<br>';
                }
                echo '<hr>';
                echo '</div>';
            }
        }
    } else {
        echo "$sectionNumaric.$elementNumaric", '&nbsp;';
        echo isset($question->elementTitle->$formDefaultLanguage) ? '<strong>' . $question->elementTitle->$formDefaultLanguage . '</strong> <br>' : (isset($question->elementTitle->$formLanguage) ? $question->elementTitle->$formLanguage : $question->elementTitle);
        echo '<div style="padding-bottom:10px;"></div>';
        if (isset($elemtvalue->postValue))
            echo (isset($elemtvalue->postValue) ? $elemtvalue->postValue : '') . '<br>';
        else
            echo ''; //(isset($elemtvalue)  ? $elemtvalue : '') . '<br>';    
        echo '<hr>';
    }
}

