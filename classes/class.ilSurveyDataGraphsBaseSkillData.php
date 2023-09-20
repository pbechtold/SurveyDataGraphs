<?php

use ILIAS\DI\Container;

require_once __DIR__ . "/../vendor/autoload.php";

class ilSurveyDataGraphsBaseSkillData
{
    private array $colors;
    private array $ref_ids;
    private array $original_base_skill;
    private array $questions;
    private array $sum_original_thresholds;
    private array $original_thresholds;

    private Container $dic;
    private array $level_data;
    private int $max_level_score;

    public function __construct(array $a_svy_ref_ids){
        global $DIC;
        $this->dic = $DIC;
        $this->ref_ids = $a_svy_ref_ids;
    }

    public function validateSurveySelection() : bool|string
    {
        $svy_base_skills = [];
        $original_base_skill = [];
        $original_thresholds = [];
        $origin_thresholds = [];
        $q_skill = [];
        $q_skills = [];
        $level_data = [];
        $level_values = [];


        foreach ($this->ref_ids as $ref_id) {


            $obj_id = ilObject::_lookupObjectId(intval($ref_id));
            if(ilObjSurvey::_lookupType($obj_id) !== "svy"){
                return "Survey Ref_Id: " . $ref_id . " not exist!";
            }
            $obj_survey = new ilObjSurvey($ref_id);
            if($obj_survey->getOfflineStatus()){
                return " Ref_Id: " . $ref_id . " is offline";
            }
            $svy_skill_obj = new ilSurveySkill($obj_survey);
            $base_skill_item = $svy_skill_obj->getAllAssignedSkillsAsOptions();
            ksort($base_skill_item, SORT_NUMERIC );

            if(empty($base_skill_item)){
                return " Ref_Id: " . $ref_id . " has no competencies!";
            }


            $svy_skill_threshold = new ilSurveySkillThresholds($obj_survey);
            $thresholds = $svy_skill_threshold->getThresholds();
            if(empty($thresholds)){
                return " Ref_Id: " . $ref_id . " has no Competence Thresholds!";
            }

            $svy_base_skills[] = $base_skill_item;

            if(isset($svy_base_skills)){
                foreach ($svy_base_skills as $svy_base_skill) {
                    if(!empty(array_diff_key($svy_base_skills[array_key_first($svy_base_skills)], $svy_base_skill))){
                        return "Different Survey competencies!";
                    }
                    foreach ($svy_base_skills[array_key_first($svy_base_skills)] as $key => $value) {
                        $ids = explode(":", $key);
                        $skl_id = intval($ids[0]);
                        $tref_id = intval($ids[1]);
                        $lvl_data = $this->dic->skills()->internal()->repo()->getLevelRepo()->getLevelData($skl_id);
                        if(!empty($lvl_data)){
                            $level_values[] = count($lvl_data);
                        }
                        foreach ($lvl_data as $level) {
                            $level_data[$skl_id][$level['id']] = $level;

                            if(isset($thresholds[$level['id']])){
                                $original_thresholds[$skl_id][$level['id']][$obj_id] = [
                                    "id" => $level['id'],
                                    "threshold" => array_sum($thresholds[$level['id']])
                                ];
                                $origin_thresholds[$skl_id][$obj_id][$level['id']] = [
                                    "id" => $level['id'],
                                    "threshold" => array_sum($thresholds[$level['id']])
                                ];
                            }

                        }
                        $question_for_skill = $svy_skill_obj->getQuestionsForSkill($skl_id, $tref_id);
                        if (empty($question_for_skill)){
                            return " Ref_Id: " . $ref_id . " has no Competence Items ";
                        }
                        $q_skill[$skl_id][$obj_id] = array_flip($question_for_skill);

                        $original_base_skill[$skl_id] = [
                            "skill_id" => $skl_id,
                            "title" => $value,
                            "tref_id" => $tref_id,
                        ];
                    }
                }
            }
        }
        $sum_o_threshold = [];
        foreach ($original_thresholds as $skl_id => $original_threshold) {
            foreach ($original_threshold as $lvl_id => $item) {
                $sum_o_threshold[$skl_id][$lvl_id] = array_sum(array_column($item, 'threshold'));
            }
        }
        $this->original_base_skill = $original_base_skill;
        $this->level_data = $level_data;
        $this->questions = $q_skill;
        $this->sum_original_thresholds = $sum_o_threshold;
        $this->original_thresholds = $origin_thresholds;

        $this->setColors(count($original_base_skill));
        $this->max_level_score = max($level_values);

        return true;
    }

    public function getOriginalSvyBaseSkills() : array
    {
        return $this->original_base_skill;
    }

    public function getSumThresholds() : array
    {
        return $this->sum_original_thresholds;
    }

    public function getThresholds() : array
    {
        return $this->original_thresholds;
    }

    public function getQuestions() : array
    {
        return $this->questions;
    }

    public function getColors() : array
    {
        return $this->colors;
    }

    public function getLevelData() : array
    {
        return $this->level_data;
    }

    public function getMAXLevelValue() : int
    {
        return $this->max_level_score;
    }

    private function setColors(int $a_number) : void
    {
        $result = [];
        $colors = $this->digikosStyleColors();
        for ($i = 0; $i < $a_number; $i++) {
            foreach ($colors as $color){
                $result[] = $color;
            }
        }
        $this->colors = $result;
    }

    private function digikosStyleColors(): array
    {
        return [
            '#E69A81',
            '#D5573D',
            '#EE8002',
            '#DEBE3B',
            '#C2CB8C',
            '#53B8CA',
            '#95B5C7',
            '#466D86',
            '#008581',
            '#C4E0D8',
            '#A4A3B4',
            '#69687F',
            '#A7A6A0',
            '#A779A0',
            '#B2546E',
        ];
    }
}
