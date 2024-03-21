<?php
namespace NazmulIslam\Utility\Components;

use NazmulIslam\Utility\Models\NazmulIslam\Utility\EmailTemplate;
use Illuminate\Database\Capsule\Manager as DB;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\EmailTag;
use Exception;

/**
 * Class EmailTemplateComponent. This class encompasses one NazmulIslam\Utility\Model\Eloquent\EmailTemplate
 * and performs methods related to that object such as CRUD and replacing the template tags.
 * @package NazmulIslam\Utility\Components
 */
class EmailTemplateComponent extends BaseComponent
{
    /**
     * The object that is manipulated by the methods of this component
     * @var EmailTemplate
     */
    private $emailTemplate;

    /**
     * These are the accessible constants that can be read for replacing tags with data
     * These tags can be put into every email so be careful not to add security or data related ones.
     * @var array
     */
    private $accessibleConstants = [
        
    ];

    /**
     * The header html prepended to every email.
     * @var string
     */
    private $emailHeader = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Email</title>
        <style type="text/css">
            body {
                background: #FFFFFF;
                margin: 0;
                padding: 20px 0;
                color: #000000;
                font-family: Helvetica,serif;
                font-size: 14px;
            }
            table {
                padding: 10px;
                width: 550px;
                background: white;
                border: 3px solid #eeeeee;
            }
            td {
                line-height: 18px;
            }
        </style>
    </head>
    <body>';

    /**
     * The footer html appended to every email
     * @var string
     */
    private $emailFooter = '
    </body>
    </html>
    ';

    /**
     * EmailComponent constructor. Takes 4 arguments, 1. EmailTemplate, 2. id 3. identifier 4. null. Passing null will
     * create a new EmailTemplate.
     * @param $id : Email | Integer | String Identifier | null
     */
    public function __construct($id = null) {
        if($id instanceof EmailTemplate) {
            $this->emailTemplate = $id;
        } else if($id !== null  && is_string($id) && !intval($id)) {
            //identifier passed
            $this->emailTemplate = EmailTemplate::where('email_identifier', $id)->first();
        } else if($id !== null) {
            //finds by id
            $this->emailTemplate = EmailTemplate::find($id);
        } else {
            //creates new entity
            $this->emailTemplate = new EmailTemplate();
        }
    }

    /**
     * Gets the email template
     * @return EmailTemplate
     */
    public function getEmailTemplate(): EmailTemplate
    {
        return $this->emailTemplate;
    }

    //Todo Make this work with the slim entrypoint. These constants are not defined when using the slim bootstrap

    /**
     * merges the constant variables with the template data array.
     * @param array $data
     */
    private function mergeConstantsWithData(array &$data) {
        //Gets global constants defined by the user
        $constants = get_defined_constants(true)['user'];
        foreach($this->accessibleConstants as $constName) {
            //If the constant name is defined as a constant
            if(array_key_exists($constName,$constants)) {
                //if the data doesn't have the same key then add the constant value
                if(array_key_exists($constName,$data) === false) {
                    $data[$constName] = $constants[$constName];
                }
            }
        }
    }

    /**
     * replaces any {{tags}} in the email templates with their key in the data array.
     * Prepends and Appends the $emailHeader and $emailFooter
     * @param array $data
     * @return string template
     */
    public function replaceTemplateTags(array $data):string {
        $this->mergeConstantsWithData($data);
        //If int passed than gets emailtemplate
        $template = $this->emailTemplate->template;
        foreach($data as $key => $value) {
            //don not use replace function for keys containing array
            if(isset($data[$key]) && $data[$key]!=NULL && is_array($data[$key])){
               
            }
            else{
                $replaceNewLineWithBreakLine = str_replace("\n", "<br>", $value);

                $template = str_replace('{{'.$key.'}}', $replaceNewLineWithBreakLine,$template);

            }
            
        }
        return $this->emailHeader . $template . $this->emailFooter;
        
    }

    /**
     * Updates an email template. Takes a key value array. The values must have the same key as the column
     * name in the emails table.
     * @param array $data
     * @return boolean
     */
    public function update(array $data): bool
    {

        $tags = $this->checkForExistingOrNewTags($data['tags']);
        $newTags = $tags['newTags'];
        $newTagsThatExist = $tags['newTagsThatExist'];
        $deletedTagIds = [];
        $currentTags = $this->emailTemplate->tags;
        //Gets ids of delete tags
        foreach ($currentTags as $cTag) {
            $found = false;
            foreach ($data['tags'] as $dTag) {
                if ($cTag->id == $dTag['value']) {
                    $found = true;
                    break;
                }
            }
            if ($found == false) {
                $deletedTagIds[] = $cTag->id;
            }
        }


        try{
            DB::connection('app')->beginTransaction();
            //Removes deleted tags
            if (count($deletedTagIds) > 0) {
                $this->emailTemplate->tags()->detach($deletedTagIds);
            }

            //creates a new tag and attaches it to the email Template
            foreach ($newTags as $tag) {
                $oldtag = EmailTag::where([['name', '=', $tag]])->first();
                if($oldtag == null) {
                    $newTag = EmailTag::create($tag);
                } else {
                    $newTag = $oldtag;
                }
                $this->emailTemplate->tags()->attach($newTag->id);
            }
            //Attaches exists tags in the db which are new to the template
            $this->emailTemplate->tags()->attach($newTagsThatExist);
            $this->emailTemplate->update($data);
            DB::connection('app')->commit();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Deletes an email template. The template will not delete if it has any related emails due to the
     * On Delete RESTRICT foreign key on the emails table.
     * @return bool
     */
    public function delete(): bool
    {
        try {
            $this->emailTemplate->delete();
            return true;
        } catch(\Exception $ex) {
            return false;
        }
    }


    /**
     * Creates an email template. Takes a key value array. The values must have the same key as the column
     * name in the emails table.
     * @param $input
     * @return bool
     */
    public function create(array $input): bool
    {
        $tags = $this->checkForExistingOrNewTags($input['tags']);
        $newTags = $tags['newTags'];
        $newTagsThatExist = $tags['newTagsThatExist'];
        $this->emailTemplate->fill($input);
        try {
            DB::connection('app')->beginTransaction();
            $this->emailTemplate->save();
            //creates a new tag and attaches it to the email Template
            foreach ($newTags as $tag) {
                $oldtag = EmailTag::where([['name', '=', $tag]])->first();
                if($oldtag == null) {
                    $newTag = EmailTag::create($tag);
                } else {
                    $newTag = $oldtag;
                }
                $this->emailTemplate->tags()->attach($newTag->id);
            }
            //Attaches exists tags in the db which are new to the template
            $this->emailTemplate->tags()->attach($newTagsThatExist);
            DB::connection('app')->commit();
            return true;
        } catch(\Exception $ex) {
            return false;
        }
    }

    /**
     * Checks if the tag values posted to the server are either new tags or existing ones
     * @param $tagData
     * @return array
     */
    private function checkForExistingOrNewTags($tagData)
    {
        $allTags = EmailTag::get();
        $newTags = [];
        $newTagsThatExist = [];
        foreach ($tagData as $tag) {
            //If the value is a number then the tag exists and hasn't been added
            if (!intval($tag['value'])) {
                //Checks if text values (new tags) exist in the tags table.
                //If they do then it adds the id to the tagsIds array.
                //If not then it will add them to the newTags array.
                $found = false;
                foreach ($allTags as $aTag) {
                    if ($aTag->name == $tag['value']) {
                        $found = true;
                        $newTagsThatExist[] = $aTag->id;
                        break;
                    }
                }
                if ($found == false) {
                    $newTags[] = ['name' => $tag['value']];
                }
            }
        }
        return [
            'newTags' => $newTags,
            'newTagsThatExist' => $newTagsThatExist,
        ];
    }


}
