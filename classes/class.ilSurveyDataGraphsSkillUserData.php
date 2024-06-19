<?php

use ILIAS\Data\URI;

require_once __DIR__ . "/../vendor/autoload.php";

class ilSurveyDataGraphsSkillUserData
{
    private ilSurveyDataGraphsDB $data;
    private array $obj_ids;
    private mixed $colors;
    private mixed $base_skills;
    private mixed $dic;
    private mixed $questions;
    private mixed $progress_level;
    private mixed $ref_ids;
    private mixed $sum_thresholds;
    private mixed $level_data;
    private mixed $chart_scale_option;
    private mixed $chart_legend_hidden_level;
    private mixed $sdg_object_title;
    private mixed $chart_title;
    private mixed $placeholder;
    private string $x_scale_title;
    private string $y_scale_title;
    private mixed $thresholds;
    private mixed $max_level_value;

    public function __construct(array $a_properties){

        global $DIC;
        $this->dic = $DIC;
        
        $this->base_skills = json_decode($a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SI_SKILLDATA], true);
        $this->level_data = json_decode($a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_LEVELDATA], true);
        $this->colors = json_decode($a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SI_COLOR], true);

        $this->sum_thresholds = json_decode($a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SKL_THRESHOLDS], true);
        $this->thresholds = json_decode($a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_THRESHOLDS], true);
        $this->questions = json_decode($a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SVY_QUESTIONS], true);

        $this->sdg_object_title = $a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_PARENT_TITLE];
        $this->progress_level = $a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SI_PROC_LIMIT];
        $this->placeholder = $a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_ACCESS_PLACEHOLDER];
        $this->obj_ids = $this->getSvyObjIds(json_decode($a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SVY_SELECTION], true));
        $this->ref_ids = json_decode($a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SVY_SELECTION], true);

        $this->chart_title = $a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_DIAGRAM_TITLE];
        $this->chart_scale_option = $a_properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SCALE_SETTING_OPTION];
        $this->chart_legend_hidden_level = $a_properties[ilSurveyDataGraphsPluginGUI::EDIT_HIDDEN_LEGEND_LEVEL];
        $this->x_scale_title = $a_properties[ilSurveyDataGraphsPluginGUI::X_SCALE_TITLE];
        $this->y_scale_title = $a_properties[ilSurveyDataGraphsPluginGUI::Y_SCALE_TITLE];

        $this->max_level_value = $a_properties[ilSurveyDataGraphsPluginGUI::MAX_LEVEL_VALUE];

        $this->data = new ilSurveyDataGraphsDB();
    }

    private function getSvyObjIds(array $a_svy_ref_ids): array
    {
        $svy_obj_ids = [];
        foreach ($a_svy_ref_ids as $a_svy_ref_id) {
            $svy_obj_ids[] = ilObject::_lookupObjectId($a_svy_ref_id);
        }
        return $svy_obj_ids;
    }

    public function getRequirements(): bool
    {
        $fi_svy_objs = $this->data->getFinishedSvyObjs($this->obj_ids);
        if(!empty(array_diff($this->obj_ids,$fi_svy_objs))){
            return false;
        }
        foreach ($fi_svy_objs as $obj_id) {
            if(!($this->data->getSvyProgress($obj_id) >= $this->progress_level)){
                return false;
            }
        }
        return true;
    }

    public function getResultListData(): array
    {
        $i = 1;
        $result = [];
        foreach ($this->obj_ids as $obj_id){
            $result[] = [
                "id" => $i++,
                "date" => $this->data->getFinishedSvyTimestamp($obj_id),
                "answers" => $this->data->getSvyAnswers($obj_id),
                "questions" => $this->data->getSvyQuestions($obj_id)
            ];
        }
        return $result;
    }

    public function getBaseSkillData(): array
    {
        return $this->base_skills;
    }

    public function getObjIds(): array
    {
        return $this->obj_ids;
    }

    public function getChartPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function getTplTitle(): string
    {
        return $this->sdg_object_title;
    }

    public function getTplClassname(): string
    {
        if(count($this->obj_ids) === 1){
            $result =  "div_" . ilSurveyDataGraphsPluginGUI::SINGLE_SI;
        }else{
            $result =  "div_" . ilSurveyDataGraphsPluginGUI::MULTI_SI;
        }
        return $result;
    }

    private function getSvyAnswerSkillValues($a_obj_id) : array
    {
        $finishedAnswerSkillData = $this->data->getFinishedAnswerSkillData($a_obj_id);
        $result = [];
        foreach ($this->base_skills as $skl_id => $base_skill) {
            if(isset($finishedAnswerSkillData[$skl_id])){
                $result[$skl_id] = intval($finishedAnswerSkillData[$skl_id]['sum_skill_value']);
            }else{
                $result[$skl_id] = 0;
            }
        }
        return $result;
    }

    private function getDynamicThresholdsForSvy($a_obj_id) : array
    {
        $result = [];
        foreach ($this->thresholds as $skl_id => $threshold) {
            $count_questions = count($this->questions[$skl_id][$a_obj_id]);
            $count_answers = count($this->data->getBaseSkillAnswers($a_obj_id, $skl_id));
            if(isset($threshold[$a_obj_id])){
                foreach ($threshold[$a_obj_id] as $item) {
                    $result[$skl_id][$item['id']] = ($item['threshold'] / $count_questions * $count_answers);
                }
            }
        }
        return $result;
    }

    private function getSkillEvaluationForSvyObjects() : array
    {
        $result = [];
        foreach ($this->obj_ids as $obj_id) {
            $svy_answer_values = $this->getSvyAnswerSkillValues($obj_id);
            $svy_skill_threshold = $this->getDynamicThresholdsForSvy($obj_id);

            foreach ($svy_skill_threshold as $skl_id => $threshold) {
                $count_q = count($this->questions[$skl_id][$obj_id]);
                $count_a = count($this->data->getBaseSkillAnswers($obj_id, $skl_id));
                $points = $svy_answer_values[$skl_id];
                foreach ($threshold as $lvl_id => $value) {
                    $level_data = $this->level_data[$skl_id][$lvl_id];
                    if($points <= $value && $points != 0){
                        $result[$skl_id][$obj_id] = [
                            "skill_id" => $skl_id,
                            "level_id" => $lvl_id,
                            "questions" => $count_q,
                            "answers" => $count_a,
                            "points" => $points,
                            "points_answer" => $count_a >= 1 ? ($points/$count_a) : $points,
                            "level_title" => $level_data['title'],
                            "level" => intval($level_data['nr']),
                        ];
                        break;
                    }elseif($points == 0){
                        $result[$skl_id][$obj_id] = [
                            "skill_id" => $skl_id,
                            "level_id" => $lvl_id,
                            "questions" => $count_q,
                            "answers" => 0,
                            "points" => 'NaN',
                            "points_answer" => 'NaN',
                            "level_title" => "ohne Wertung",
                            "level" => 'NaN',
                        ];
                        break;
                    }
                }
            }
        }
        return $result;
    }
    private function getChartRunData() : array
    {
        $runs = [];
        for($i = 0; $i < count($this->obj_ids); $i++){
            $runs[] = $i+1 ;
        }
        return $runs;
    }
    private function getChartDSData() : array|string
    {
        $eval = $this->getSkillEvaluationForSvyObjects();
        $result = [];
        foreach ($eval as $key => $value) {
            if($this->chart_scale_option === ilSurveyDataGraphsPluginGUI::VIEW_SCALE_SETTING_OPTION_LEVEL){
                $result[$key] = implode(",", array_column($value, 'level'));
            }elseif ($this->chart_scale_option === ilSurveyDataGraphsPluginGUI::VIEW_SCALE_SETTING_OPTION_POINTS){
                $result[$key] = implode(",", array_column($value, 'points_answer'));
            }elseif ($this->chart_scale_option === ilSurveyDataGraphsPluginGUI::VIEW_SCALE_SETTING_OPTION_POINTS_TOTAL){
                $result[$key] = implode(",", array_column($value, 'points'));
            }
        }
        return $result;
    }
    public function getChartData() : array
    {
        return [
            "chart_title" => $this->chart_title,
            "chart_type" => count($this->obj_ids) === 1 ? 'bar' : 'line',
            "runs" => count($this->obj_ids) === 1 ? json_encode([""]) : json_encode($this->getChartRunData()),
            "index_axis" => count($this->obj_ids) === 1 ? 'y' : 'x',
            "skl_data" => $this->getChartDSData(),
            "skl_max_value" => max($this->getChartDSData()),
            "color" => $this->colors,
            "hidden_data_label" => $this->chart_legend_hidden_level,
            "x_scale_title" => $this->x_scale_title,
            "y_scale_title" => $this->y_scale_title,
        ];
    }

    private function getAnswerDataForAll() : array
    {
        $count_questions = [];
        $count_answers = [];
        $points = [];

        foreach ($this->obj_ids as $obj_id) {
            foreach ($this->base_skills as $skl_id => $base_skill) {
                $count_questions[$skl_id][$obj_id] = count($this->questions[$skl_id][$obj_id]);
                $count_answers[$skl_id][$obj_id] = count($this->data->getBaseSkillAnswers($obj_id, $skl_id));
                $points[$skl_id][$obj_id] = array_sum(array_column($this->data->getBaseSkillAnswers($obj_id, $skl_id), "points"));
            }
        }

        $result = [];

        foreach ($this->base_skills as $skl_id => $base_skill) {
            $result[$skl_id] = [
                "questions" => array_sum($count_questions[$skl_id]),
                "answers" => array_sum($count_answers[$skl_id]),
                "points" => array_sum($points[$skl_id])];
        }

        return $result;
    }

    private function skillLevelImg(int $a_level): string
    {
        $path = match ($a_level) {
            1 => "Customizing/global/plugins/Services/COPage/PageComponent/SurveyDataGraphs/images/skl_gap_red.png",
            2 => "Customizing/global/plugins/Services/COPage/PageComponent/SurveyDataGraphs/images/skl_gap_yellow.png",
            3 => "Customizing/global/plugins/Services/COPage/PageComponent/SurveyDataGraphs/images/skl_gap_green.png",
            default => "",
        };

        return '<img style="width: 100px; float: left;" src="'. $path .'">';
    }

    private function getDynThresholdsForAll() : array
    {
        $new_base_skill_thresholds = [];
        $sum_thresholds = $this->sum_thresholds;
        $count = $this->getAnswerDataForAll();

        foreach ($sum_thresholds as $skl_id => $base_skill) {

            $count_q = $count[$skl_id]['questions'];
            $count_a = $count[$skl_id]['answers'];

            foreach ($base_skill as $lvl_id => $base_skill_threshold) {
                if ($count_a >= 1){
                    $new_base_skill_thresholds[$skl_id][$lvl_id] = ($base_skill_threshold / $count_q) * $count_a;
                }else{
                    $new_base_skill_thresholds[$skl_id][$lvl_id] = 0;
                }
            }
        }

        return $new_base_skill_thresholds;
    }
    private function getLevel() : array
    {
        $svy_answer_values = $this->getAnswerDataForAll();
        $svy_skill_thresholds = $this->getDynThresholdsForAll();
        $result = [];

        foreach ($svy_skill_thresholds as $skl_id => $thresholds) {
            foreach ($thresholds as $lvl_id => $threshold) {

                $points = $svy_answer_values[$skl_id]['points'];
                $lvl_datails = $this->level_data[$skl_id][$lvl_id];

                if($points <= $threshold && $points != 0){
                    $result[$skl_id] = [
                        "skill_id" => $skl_id,
                        "title" => $this->base_skills[$skl_id]['title'],
                        "level_id" => $lvl_id,
                        "questions" => $svy_answer_values[$skl_id]['questions'],
                        "answers" => $svy_answer_values[$skl_id]['answers'],
                        "points" => $points,
                        "level_title" => $lvl_datails['title'],
                        "level" => $lvl_datails['nr'],
                        "description" => $lvl_datails["description"],
                        "img" => $this->skillLevelImg(intval($lvl_datails['nr'])),
                        "resource" => $this->data->getSkillResourceRefId(intval($lvl_id)),
                    ];
                    break;
                }elseif($points == 0){
                    $result[$skl_id] = [
                        "skill_id" => $skl_id,
                        "title" => $this->base_skills[$skl_id]['title'],
                        "level_id" => $lvl_id,
                        "questions" => $svy_answer_values[$skl_id]['questions'],
                        "answers" => $svy_answer_values[$skl_id]['answers'],
                        "points" => $points,
                        "level_title" => "ohne Wertung",
                        "level" => 0,
                        "description" => "",
                        "img" => $this->skillLevelImg(intval($lvl_datails['nr'])),
                        "resource" => [],
                    ];
                    break;
                }
            }
        }

        return $result;
    }
    public function getSkillResourceLinks(int $a_ref_id) : string
    {
        $f = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();

        $obj_id = ilObject::_lookupObjectId($a_ref_id);
        $target = new URI(ilLink::_getLink($a_ref_id));
        $title = ilObject::_lookupTitle($obj_id);
        $type = ilObject::_lookupType($obj_id);

        $object_icon = $f->symbol()->icon()->standard($type, $type, 'medium');

        return $renderer->render($f->link()->bulky($object_icon, $title, $target));
    }

    private function skillColorIcon(int $a_level) : string
    {
        $color = match ($a_level) {
            1 => "#D5573D",
            2 => "#DEBE3B",
            3 => "#008581",
            default => "#ffffff",
        };

        return '<svg xmlns="http://www.w3.org/2000/svg" style="float: left" width="25" height="25" viewBox="0 0 30 30"> '
            . ' <circle cx="12.5" cy="12.5" r="10" stroke="black" stroke-width="0.2" fill="' . $color . '" /> </svg>';
    }

    public function sklEvalTblData() : array
    {
        $levels = $this->getLevel();
        $skill_list = [];

        foreach ($levels as $level) {

            $resource_link_list = [];

            foreach ($level['resource'] as $r_link){
                $resource_link_list[] = ['link' => $r_link['rep_ref_id']];
            }
            $skill_list[] = [
                'type' => 'Single Choice Frage',
                'img' => $this->skillLevelImg($level['level']),
                'img2' => $this->skillColorIcon($level['level']),
                'skill_title' => $level['title'],
                'skill_txt' => 'Wie ausgeprägt ist ' . $level['title'] . ' des / der Studierenden?',
                'description' => $level['description'],
                'level' => $level['level'],
                'level_title' => $level['level_title'],
                'links' => $resource_link_list,
                'stats' => array(
                    'questions' => $level['questions'],
                    'answered' => $level['answers'],
                    'skipped' => $level['questions'] - $level['answers'],
                    'points_total' => $level['points'],
                    'proportion' => round((($level['answers']/$level['questions'])*100),2) . '%'
                )
            ];
        }
        return $skill_list;
    }

    public function environment() : array
    {

        $skill_result = function ($links){
            $result = "";
            if(empty($links)){
                $result = 'Keine Lernmaterialien vorhanden';
            }else{
                foreach ($links as $link){
                    $result .= '<div style="padding-left: 45px">' . $this->getSkillResourceLinks($link['link']) . '</div>';
                }
            }
            return $result;
        };

        return  array('skill_result' => $skill_result,);
    }

    public function getMAXQuestValue() : int
    {
        $result = [];
        foreach ($this->obj_ids as $obj_id) {
            $result[] = $this->data->getMAXQuestionValue($obj_id);
        }
        return max($result);
    }

    public function getMAXQuestTotalValue() : int
    {
        $levels = $this->getLevel();
        $result = [];
        foreach ($this->base_skills as $base_skill){
            $result[] = $levels[$base_skill['skill_id']]['answers'] * $this->getMAXQuestValue();
        }
        return max($result);
    }

    public function getMAXQuestLevel() : int
    {
        return $this->max_level_value;
    }
}