<?php

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

require_once __DIR__ . "/../vendor/autoload.php";

class ilSurveyDataGraphsPresentationGUI
{
    const SI_RESULT_ID_HEADER = "si_result_id_header";
    const SI_RESULT_DATE_HEADER = "si_result_date_header";
    const SI_RESULT_ANSWERED_HEADER = "si_result_answered_header";
    private mixed $plugin;
    private static int $id_counter = 0;
    private mixed $properties;
    private ilSurveyDataGraphsSkillUserData $data;
    private Factory $factory;
    private Renderer $renderer;
    private ilLogger $logger;

    public function __construct($a_plugin, $a_properties)
    {
        global $DIC;
        $this->logger = ilLoggerFactory::getLogger('surveydatagraphs');
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->data = new ilSurveyDataGraphsSkillUserData($a_properties);
        $this->properties = $a_properties;
        $this->plugin = $a_plugin;
        self::$id_counter ++;
    }

    /**
     * @throws \ilTemplateException
     */
    private function svyDetailTableTpl(): string
    {
        $data = $this->data->getResultListData();
        $tpl = $this->plugin->getTemplate("tpl.svy-result-list.html");

        $tpl->setVariable("SI_RESULT_ID_HEADER", $this->plugin->txt(self::SI_RESULT_ID_HEADER));
        $tpl->setVariable("SI_RESULT_DATE_HEADER", $this->plugin->txt(self::SI_RESULT_DATE_HEADER));
        $tpl->setVariable("SI_RESULT_ANSWERED_HEADER", $this->plugin->txt(self::SI_RESULT_ANSWERED_HEADER));

        foreach ($data as $item){
            if(!empty($item['date'])){
                $tpl->setCurrentBlock("si-result-list-row");
                $tpl->setVariable("SI_RESULT_ID", ($item["id"]));
                $tpl->setVariable("SI_RESULT_DATE", $item["date"]);
                $tpl->setVariable("SI_RESULT_ANSWERS", count($item["answers"]));
                $tpl->setVariable("SI_RESULT_QUESTIONS", count($item["questions"]));
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }

    /**
     * @throws \ilTemplateException
     */
    private function getChartTpl(): string
    {
        $chart_data = $this->data->getChartData();
        $tpl = $this->plugin->getTemplate("tpl.chart.html");

        $tpl->setVariable("CHART_TITLE", $chart_data['chart_title']);
        $tpl->setVariable("CHARTID",  "sdg_" . self::$id_counter);
        $tpl->setVariable("CHART_DS_BORDER", 1);
        $tpl->setVariable("CHART_TYPE", $chart_data['chart_type']);
        $tpl->setVariable("INDEX_AXIS", $chart_data['index_axis']);
        $tpl->setVariable("SVY_RUNS", $chart_data['runs']);

        if($chart_data['index_axis'] === 'x'){
            $tpl->setVariable("CHART_Y_SCALE_MAX", $this->getChartScaleIndex());
            $tpl->setVariable("CHART_X_SCALE_MAX", count(json_decode($chart_data['runs'])));
            $tpl->setVariable("CHART_X_SCALE_MIN", 1);
            $tpl->setVariable("CHART_Y_SCALE_MIN", 1);
        }elseif ($chart_data['index_axis'] === 'y'){
            $tpl->setVariable("CHART_Y_SCALE_MAX", 1);
            $tpl->setVariable("CHART_X_SCALE_MAX", $this->getChartScaleIndex());
            $tpl->setVariable("CHART_X_SCALE_MIN", 0);
            $tpl->setVariable("CHART_Y_SCALE_MIN", 0);
        }

        $tpl->setVariable("CHART_Y_SCALE_TITLE", $chart_data['y_scale_title']);
        $tpl->setVariable("CHART_X_SCALE_TITLE", $chart_data['x_scale_title']);
        $tpl->setVariable("LEGEND_DISPLAY", true);

        if($this->data->getRequirements()){
            $count = 0;
            foreach ($this->data->getBaseSkillData() as $skl_id => $skill){

                $this->logger->info(sprintf(
                    "getChartTpl() 92: %s", $skl_id
                ));

                $tpl->setCurrentBlock("CHART_DATASET");
                $tpl->setVariable("CHART_DS_LABEL", $skill['title']);
                $tpl->setVariable("CHART_BACKGROUND_COLOR", "white");

                if(isset($chart_data['color'][$count])){
                    $tpl->setVariable("CHART_DS_BACKGROUND_COLOR", $chart_data['color'][$count]);
                    $tpl->setVariable("CHART_DS_BORDER_COLOR", $chart_data['color'][$count]);
                }

                if(isset($chart_data['skl_data'][$skl_id])){
                    $tpl->setVariable("CHART_DS_DATA", $chart_data['skl_data'][$skl_id]);
                    $this->logger->info(sprintf(
                        "getChartTpl() 106: %s", $chart_data['skl_data'][$skl_id]
                    ));
                }

                if($count < $chart_data['hidden_data_label']){
                    $tpl->setVariable("HIDDEN_DATA_LABEL", false);
                }else{
                    $tpl->setVariable("HIDDEN_DATA_LABEL", true);
                }



                $tpl->parseCurrentBlock();
                $count++;
            }
        } else{
            foreach ($this->data->getBaseSkillData() as $value){
                $tpl->setCurrentBlock("CHART_DATASET");
                $tpl->setVariable("CHART_DS_LABEL", $value['title']);
                $tpl->setVariable("CHART_DS_DATA", "");
                $tpl->setVariable("CHART_DS_BACKGROUND_COLOR", "#000");
                $tpl->setVariable("CHART_DS_BORDER_COLOR", "#000");
                $tpl->setVariable("LEGEND_DISPLAY", false);
                $tpl->setVariable("CHART_BACKGROUND_COLOR", "#ffffff80");
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }

    private function getChartScaleIndex(): int
    {
        $result = 0;
        if ($this->properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SCALE_SETTING_OPTION] === ilSurveyDataGraphsPluginGUI::VIEW_SCALE_SETTING_OPTION_LEVEL){
            $result = $this->data->getMAXQuestLevel();
        }elseif ($this->properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SCALE_SETTING_OPTION] === ilSurveyDataGraphsPluginGUI::VIEW_SCALE_SETTING_OPTION_POINTS){
            $result = $this->data->getMAXQuestValue();
        }elseif ($this->properties[ilSurveyDataGraphsPluginGUI::EDIT_CONF_SCALE_SETTING_OPTION] === ilSurveyDataGraphsPluginGUI::VIEW_SCALE_SETTING_OPTION_POINTS_TOTAL){
            $result = $this->data->getMAXQuestTotalValue();
        }
        return $result;
    }

    /**
     * @throws \ilTemplateException
     */
    public function getContentTpl(): string
    {

        $tpl = $this->plugin->getTemplate("tpl.content.html");
        $tpl_access = $this->plugin->getTemplate("tpl.access-container.html");

        if ($this->data->getRequirements()){
            $tpl->setVariable("SKL_NAV", $this->sklEvaluationTableGUI());
        }else{

            $tpl_access->setVariable("ACCESS_ITEM", $this->data->getChartPlaceholder());
            $tpl->setVariable("ACCESS", $tpl_access->get());
        }

        $tpl->setVariable("TPL_CLASS", $this->data->getTplClassname());
        $tpl->setVariable("TPL_TITLE", $this->data->getTplTitle());
        $tpl->setVariable("CHART_TPL", $this->getChartTpl());
        $tpl->setVariable("SI_RESULT_LIST_TBL", $this->svyDetailTableTpl());

        return $tpl->get();
    }

    /**
     * @throws \ilTemplateException
     */
    private function sklEvaluationTableGUI() : string
    {
        $view_controls = array();
        $mapping_closure = function ($row, $record, $ui_factory, $environment) {
            return $row
                ->withHeadline($record['skill_title'])
                ->withImportantFields(
                    array(
                        'Ausprägung: ' => $record['level_title'] . $record['img2'],
                        'Beantwortet: ' => $record['stats']['answered'] . " von " . $record['stats']['questions'] . " - " . $record['stats']['proportion'],
                    )
                )
                ->withContent(
                    $ui_factory->listing()->descriptive(
                        array(
                            $record['level_title'] => $record['img'] . $record['description'],
                        )
                    )
                )
                ->withFurtherFieldsHeadline("<strong>" . "Empfohlene Lernmaterialien" . "</strong>")
                ->withFurtherFields(array("" => $environment['skill_result']($record['links'])));
        };

        $table = $this->factory->table()->presentation(
            "",
            $view_controls,
            $mapping_closure
        )->withEnvironment($this->data->environment());
        $data = $this->data->sklEvalTblData();

        $panel = $this->factory->panel()->standard(
            "Individuelle Ausprägungen",
            $this->factory->legacy(
                "<div style='overflow-y: scroll; height:400px;'>" .
                $this->renderer->render($table->withData($data)) .
                "</div>"
            )
        );
        return $this->renderer->render($panel);
    }
}