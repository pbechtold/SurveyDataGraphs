<?php

require_once __DIR__ . "/../vendor/autoload.php";

/**
 *
 * @ilCtrl_isCalledBy ilSurveyDataGraphsPluginGUI: ilPCPluggedGUI
 */
class ilSurveyDataGraphsPluginGUI extends ilPageComponentPluginGUI
{
    const MSG_OBJ_MODIFIED = "msg_obj_modified";
    const CMD_INSERT = "cmd_insert";
    const CMD_UPDATE = "update";
    const CMD_CREATE = "create";
    const CMD_CANCEL = "cancel";
    const CMD_EDIT = "edit";
    const CMD_SAVE = "save";
    const MODE = "mode";
    const EDIT_CONF_SI_PROC_LIMIT = "edit_conf_si_proc_limit";
    const SETTINGS1 = "settings_1";
    const SETTINGS2 = "settings_2";
    const SINGLE_SI = "simo";
    const MULTI_SI = "sile";
    const EDIT_COLOR = "editcolor";
    const UPDATE_COLOR = "updatecolor";
    const EDIT_CONF_SVY_SELECTION = 'edit_conf_svy_selection';
    const EDIT_CONF_SVY_SELECTION_INFO = 'edit_conf_svy_selection_info';
    const EDIT_CONF_SI_COLOR = 'edit_conf_si_color';
    const EDIT_CONF_SI_SKILLDATA = 'edit_conf_si_skilldata';
    const VIEW_PARENT_TITLE = 'view_parent_title';
    const VIEW_PARENT_TITLE_INFO = 'view_parent_title_info';
    const VIEW_DIAGRAM_TITLE = 'view_diagram_title';
    const VIEW_DIAGRAM_TITLE_INFO = 'view_diagram_title_info';
    const EDIT_CONF_PARENT_TITLE = 'edit_conf_parent_title';
    const EDIT_CONF_DIAGRAM_TITLE = 'edit_conf_diagram_title';
    const FORM_COLOR_SECTION_HEADER = 'form_color_section_header';
    const FORM_TEXT_SECTION_TITLE = 'form_text_section_title';
    const FORM_TEXT_SECTION_INFO = 'form_text_section_info';
    const FORM_COLOR_SECTION_HEADER_INFO = 'form_color_section_header_info';
    const VIEW_ACCESS_PLACEHOLDER = 'view_access_placeholder';
    const VIEW_ACCESS_PLACEHOLDER_INFO = 'view_access_placeholder_info';
    const EDIT_CONF_ACCESS_PLACEHOLDER = 'edit_conf_access_placeholder';
    const COLOR_FORM_DESCRIPTION = 'color_form_description';
    const COLOR_FORM_HEADER = 'color_form_header';
    const VIEW_SCALE_SETTING_HEADER = 'view_scale_setting_header';
    const VIEW_SCALE_SETTING_HEADER_INFO = 'view_scale_setting_header_info';
    const VIEW_SCALE_SETTING_GROUP = 'view_scale_setting_group';
    const VIEW_SCALE_SETTING_OPTION_LEVEL = 'view_scale_setting_option_level';
    const VIEW_SCALE_SETTING_OPTION_POINTS = 'view_scale_setting_option_points';
    const VIEW_HIDDEN_LEGEND_LEVEL = "view_hidden_legend_level";
    const VIEW_HIDDEN_LEGEND_LEVEL_INFO = "view_hidden_legend_level_info";
    const EDIT_HIDDEN_LEGEND_LEVEL = 'edit_hidden_legend_level';
    const EDIT_CONF_SVY_QUEST_VALUE_RANGE = 'edit_conf_svy_quest_value_range';
    const EDIT_CONF_SCALE_SETTING_OPTION = 'edit_conf_scale_setting_option';
    const EDIT_CONF_SVY_QUESTIONS = 'edit_conf_svy_questions';
    const VIEW_SCALE_SETTING_OPTION_POINTS_TOTAL = 'view_scale_setting_option_points_total';
    const EDIT_CONF_SKL_THRESHOLDS = 'edit_conf_skl_thresholds';
    const EDIT_CONF_SVY_QUESTIONS_SKL = 'edit_conf_svy_questions_skl';
    const EDIT_CONF_LEVELDATA = 'edit_conf_leveldata';
    const X_SCALE_TITLE = 'x_scale_title';
    const Y_SCALE_TITLE = 'y_scale_title';
    const EDIT_CONF_THRESHOLDS = 'edit_conf_thresholds';
    const EDIT_CONF_SI_PROC_LIMIT_INFO = 'edit_conf_si_proc_limit_info';
    const EDIT_CONF_FORM_HEADER = 'edit_conf_form_header';
    const EDIT_CONF_SVY_SELECTION_TITLE = 'edit_conf_svy_selection_title';
    const MAX_LEVEL_VALUE = 'max_level_value';

    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC['tpl'];
        $this->tabs = $DIC->tabs();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        if (in_array($cmd, array(self::CMD_CREATE,
                                 self::CMD_SAVE,
                                 self::CMD_EDIT,
                                 self::CMD_UPDATE,
                                 self::CMD_CANCEL,
                                 self::EDIT_COLOR,
                                 self::UPDATE_COLOR
        ))) {
            $this->$cmd();
        }
    }

    /**
     * @throws \ilFormException
     * @throws \ilCtrlException
     */
    public function insert(): void
    {
        $this->setTabs(self::CMD_EDIT);
        $form = $this->initForm(true);
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * @throws \ilCtrlException
     * @throws \ilFormException
     */
    public function create(): void
    {
        $form = $this->initForm(true);
        if ($form->checkInput())
        {
            $skilldata = new ilSurveyDataGraphsBaseSkillData($form->getInput(self::EDIT_CONF_SVY_SELECTION));
            $input_validation = $skilldata->validateSurveySelection();
            if(is_bool($input_validation)){
                $properties = $this->getProperties();
                $properties[self::EDIT_CONF_SVY_SELECTION] = json_encode($form->getInput(self::EDIT_CONF_SVY_SELECTION));
                $properties[self::EDIT_CONF_SI_PROC_LIMIT] = $form->getInput(self::EDIT_CONF_SI_PROC_LIMIT);
                $properties[self::EDIT_CONF_PARENT_TITLE] = "Deine persönlichen Ergebnisse aus SIMo im Überblick";
                $properties[self::EDIT_CONF_DIAGRAM_TITLE] = "Deine Studienmotivation";
                $properties[self::EDIT_CONF_ACCESS_PLACEHOLDER] = "Nach der Bearbeitung aller Umfragen findest du hier deinen Lernverlauf.";
                $properties[self::X_SCALE_TITLE] = "x";
                $properties[self::Y_SCALE_TITLE] = "y";
                $properties[self::EDIT_CONF_SI_SKILLDATA] = json_encode($skilldata->getOriginalSvyBaseSkills());
                $properties[self::EDIT_CONF_LEVELDATA] = json_encode($skilldata->getLevelData());
                $properties[self::EDIT_CONF_SKL_THRESHOLDS] = json_encode($skilldata->getSumThresholds());
                $properties[self::EDIT_CONF_THRESHOLDS] = json_encode($skilldata->getThresholds());
                $properties[self::EDIT_CONF_SI_COLOR] = json_encode($skilldata->getColors());
                $properties[self::EDIT_CONF_SVY_QUESTIONS] = json_encode($skilldata->getQuestions());
                $properties[self::EDIT_CONF_SCALE_SETTING_OPTION] = self::VIEW_SCALE_SETTING_OPTION_LEVEL;
                $properties[self::EDIT_HIDDEN_LEGEND_LEVEL] = count($skilldata->getOriginalSvyBaseSkills());
                $properties[self::MAX_LEVEL_VALUE] = $skilldata->getMAXLevelValue();

                if ($this->createElement($properties))
                {
                    $this->tpl->setOnScreenMessage("success", $this->lng->txt(self::MSG_OBJ_MODIFIED), true);
                    $this->returnToParent();
                }
            }else{
                $this->tpl->setOnScreenMessage("failure", $input_validation, true);
            }
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * @throws \ilFormException
     * @throws \ilCtrlException
     */
    public function edit(): void
    {
        $this->setTabs(self::CMD_EDIT);
        $form = $this->initForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function cancel(): void
    {
        $this->returnToParent();
    }

    /**
     * @throws \ilFormException
     * @throws \ilCtrlException
     */
    public function update(): void
    {
        $form = $this->initForm();
        if($form->checkInput())
        {
            $properties = $this->getProperties();
            $properties[self::EDIT_CONF_SI_PROC_LIMIT] = $form->getInput(self::EDIT_CONF_SI_PROC_LIMIT);
            if ($this->updateElement($properties))
            {
                $this->tpl->setOnScreenMessage("success", $this->lng->txt(self::MSG_OBJ_MODIFIED), true);
            }
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * @throws \ilCtrlException
     */
    public function updateColor(): void
    {
        $form = $this->initColorForm();
        if ($form->checkInput())
        {
            $properties = $this->getProperties();
            $colors = json_decode($properties[self::EDIT_CONF_SI_COLOR]);
            for ($i = 0; $i < count($colors); $i++){
                $colors[$i] = "#".$form->getInput("cp".($i));
            }
            $properties[self::EDIT_CONF_SI_COLOR] = json_encode($colors);
            $properties[self::EDIT_CONF_PARENT_TITLE] = $form->getInput(self::VIEW_PARENT_TITLE);
            $properties[self::EDIT_CONF_DIAGRAM_TITLE] = $form->getInput(self::VIEW_DIAGRAM_TITLE);
            $properties[self::EDIT_CONF_ACCESS_PLACEHOLDER] = $form->getInput(self::VIEW_ACCESS_PLACEHOLDER);
            $properties[self::EDIT_CONF_SCALE_SETTING_OPTION] = $form->getInput(self::VIEW_SCALE_SETTING_GROUP);
            $properties[self::EDIT_HIDDEN_LEGEND_LEVEL] = $form->getInput(self::VIEW_HIDDEN_LEGEND_LEVEL);
            $properties[self::X_SCALE_TITLE] = $form->getInput(self::X_SCALE_TITLE);
            $properties[self::Y_SCALE_TITLE] = $form->getInput(self::Y_SCALE_TITLE);

            if ($this->updateElement($properties))
            {
                $this->tpl->setOnScreenMessage("success", $this->lng->txt(self::MSG_OBJ_MODIFIED), true);
                $this->returnToParent();
            }
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * @throws \ilCtrlException
     * @throws \ilFormException
     */
    public function initForm($a_create = false): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->plugin->txt(self::EDIT_CONF_FORM_HEADER));
        $form->setShowTopButtons(true);

        $processing_limit = new ilNumberInputGUI($this->getPlugin()->txt(self::EDIT_CONF_SI_PROC_LIMIT), self::EDIT_CONF_SI_PROC_LIMIT);
        $processing_limit->setInfo($this->plugin->txt(self::EDIT_CONF_SI_PROC_LIMIT_INFO));
        $processing_limit->setMaxLength(40);
        $processing_limit->setSize(1);
        $processing_limit->setRequired(true);
        $processing_limit->setMaxValue(100, true);
        $processing_limit->setMinValue(0, true);

        $svy_ref_ids = new ilTextInputGUI($this->getPlugin()->txt(self::EDIT_CONF_SVY_SELECTION), self::EDIT_CONF_SVY_SELECTION);
        $svy_ref_ids->setMulti(true);
        $svy_ref_ids->setRequired(true);
        $svy_ref_ids->setInputType("number");
        $svy_ref_ids->setSize(5);
        $svy_ref_ids->setInfo($this->getPlugin()->txt(self::EDIT_CONF_SVY_SELECTION_INFO));
        $form->addItem($svy_ref_ids);
        $form->addItem($processing_limit);

        if (!$a_create)
        {
            $prop = $this->getProperties();
            $processing_limit->setValue($prop[self::EDIT_CONF_SI_PROC_LIMIT]);
            $svy_ref_ids->setValue(json_decode($prop[self::EDIT_CONF_SVY_SELECTION])[0]);
            $svy_ref_ids->setMultiValues(json_decode($prop[self::EDIT_CONF_SVY_SELECTION]));
            $svy_ref_ids->setTitle($this->plugin->txt(self::EDIT_CONF_SVY_SELECTION_TITLE));
        }
        if ($a_create)
        {
            $this->addCreationButton($form);
            $form->addCommandButton(self::CMD_CANCEL, $this->lng->txt(self::CMD_CANCEL));
            $form->setTitle($this->getPlugin()->txt(self::CMD_INSERT));
        }
        else
        {
            $svy_ref_ids->setDisabled(true);
            $form->addCommandButton(self::CMD_UPDATE, $this->lng->txt(self::CMD_SAVE));
            $form->addCommandButton(self::CMD_CANCEL, $this->lng->txt(self::CMD_CANCEL));
        }
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * @throws \ilCtrlException
     */
    public function initColorForm(): ilPropertyFormGUI
    {
        $properties = $this->getProperties();

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->plugin->txt(self::SETTINGS2));

        $text_header = new ilFormSectionHeaderGUI();
        $text_header->setTitle($this->plugin->txt(self::FORM_TEXT_SECTION_TITLE));
        $text_header->setInfo($this->plugin->txt(self::FORM_TEXT_SECTION_INFO));
        $form->addItem($text_header);

        $parent_titel = new ilTextInputGUI($this->plugin->txt(self::VIEW_PARENT_TITLE), self::VIEW_PARENT_TITLE);
        $parent_titel->setInfo($this->plugin->txt(self::VIEW_PARENT_TITLE_INFO));
        $parent_titel->setValue($properties[self::EDIT_CONF_PARENT_TITLE]);
        $parent_titel->setInputType('text');
        $form->addItem($parent_titel);

        $diagram_titel = new ilTextInputGUI($this->plugin->txt(self::VIEW_DIAGRAM_TITLE), self::VIEW_DIAGRAM_TITLE);
        $diagram_titel->setInfo($this->plugin->txt(self::VIEW_DIAGRAM_TITLE_INFO));
        $diagram_titel->setValue($properties[self::EDIT_CONF_DIAGRAM_TITLE]);
        $diagram_titel->setInputType('text');
        $form->addItem($diagram_titel);

        $access_placeholder = new ilTextInputGUI($this->plugin->txt(self::VIEW_ACCESS_PLACEHOLDER), self::VIEW_ACCESS_PLACEHOLDER);
        $access_placeholder->setInfo($this->plugin->txt(self::VIEW_ACCESS_PLACEHOLDER_INFO));
        $access_placeholder->setValue($properties[self::EDIT_CONF_ACCESS_PLACEHOLDER]);
        $access_placeholder->setInputType('text');
        $form->addItem($access_placeholder);

        $x_scale_title = new ilTextInputGUI($this->plugin->txt(self::X_SCALE_TITLE),self::X_SCALE_TITLE);
        $x_scale_title->setValue($properties[self::X_SCALE_TITLE] ?? 'x');
        $x_scale_title->setInputType('text');
        $form->addItem($x_scale_title);

        $y_scale_title = new ilTextInputGUI($this->plugin->txt(self::Y_SCALE_TITLE),self::Y_SCALE_TITLE);
        $y_scale_title->setValue($properties[self::Y_SCALE_TITLE] ?? 'y');
        $y_scale_title->setInputType('text');
        $form->addItem($y_scale_title);

        $scale_header = new ilFormSectionHeaderGUI();
        $scale_header->setTitle($this->plugin->txt(self::VIEW_SCALE_SETTING_HEADER));
        $scale_header->setInfo($this->plugin->txt(self::VIEW_SCALE_SETTING_HEADER_INFO));
        $form->addItem($scale_header);

        $scale_option_level = new ilRadioOption($this->plugin->txt(
            self::VIEW_SCALE_SETTING_OPTION_LEVEL),
            self::VIEW_SCALE_SETTING_OPTION_LEVEL
        );

        $scale_option_points = new ilRadioOption($this->plugin->txt(
            self::VIEW_SCALE_SETTING_OPTION_POINTS),
            self::VIEW_SCALE_SETTING_OPTION_POINTS
        );

        $scale_option_points_total = new ilRadioOption($this->plugin->txt(
            self::VIEW_SCALE_SETTING_OPTION_POINTS_TOTAL),
            self::VIEW_SCALE_SETTING_OPTION_POINTS_TOTAL
        );

        $scale_option_points_total->setDisabled(true);

        $scale_option_group = new ilRadioGroupInputGUI($this->plugin->txt(self::VIEW_SCALE_SETTING_GROUP), self::VIEW_SCALE_SETTING_GROUP);
        $scale_option_group->addOption($scale_option_level);
        $scale_option_group->addOption($scale_option_points);
        $scale_option_group->addOption($scale_option_points_total);
        $scale_option_group->setValue($properties[self::EDIT_CONF_SCALE_SETTING_OPTION]);
        $form->addItem($scale_option_group);

        $skill_data = json_decode($properties[self::EDIT_CONF_SI_SKILLDATA], true);
        $sd_title = array_column($skill_data,"title");

        $hidden_legend_level = new ilNumberInputGUI($this->plugin->txt(self::VIEW_HIDDEN_LEGEND_LEVEL), self::VIEW_HIDDEN_LEGEND_LEVEL);
        $hidden_legend_level->setInfo($this->plugin->txt(self::VIEW_HIDDEN_LEGEND_LEVEL_INFO));
        $hidden_legend_level->allowDecimals(false);
        $hidden_legend_level->setMaxValue(count($sd_title), true);
        $hidden_legend_level->setMinValue(0, true);
        $hidden_legend_level->setSize(1);
        $hidden_legend_level->setValue($properties[self::EDIT_HIDDEN_LEGEND_LEVEL] ?? count($sd_title));
        $form->addItem($hidden_legend_level);

        $color_header = new ilFormSectionHeaderGUI();
        $color_header->setTitle($this->plugin->txt(self::FORM_COLOR_SECTION_HEADER));
        $color_header->setInfo($this->plugin->txt(self::FORM_COLOR_SECTION_HEADER_INFO));
        $form->addItem($color_header);

        $colors = json_decode($properties['edit_conf_si_color']);

        for($i = 0; $i < count($sd_title); $i++){
            $cp = new ilColorPickerInputGUI($sd_title[$i], "cp".($i));
            $cp->setValue($colors[$i]);
            $form->addItem($cp);
        }

        $form->addCommandButton(self::UPDATE_COLOR, $this->lng->txt(self::CMD_SAVE));
        $form->addCommandButton(self::CMD_CANCEL, $this->lng->txt(self::CMD_CANCEL));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * @throws \ilTemplateException
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        $presentation = new ilSurveyDataGraphsPresentationGUI($this->plugin, $a_properties);
        return $presentation->getContentTpl();
    }

    /**
     * @throws \ilCtrlException
     */
    public function setTabs(string $a_active): void
    {
        $this->tabs->addTab(
            self::CMD_EDIT, $this->plugin->txt(self::SETTINGS1),
            $this->ctrl->getLinkTarget($this, self::CMD_EDIT)
        );
        $properties = $this->getProperties();
        if(isset($properties[self::EDIT_CONF_SVY_SELECTION])){
            $this->tabs->addTab(
                self::EDIT_COLOR, $this->plugin->txt(self::SETTINGS2),
                $this->ctrl->getLinkTarget($this,self::EDIT_COLOR)
            );
        }
        $this->tabs->activateTab($a_active);
    }

    /**
     * @throws ilDatabaseException
     * @throws \ilCtrlException
     */
    public function editcolor() :void
    {
        global $tpl;

        $this->setTabs(self::EDIT_COLOR);
        $form = $this->initColorForm();
        $tpl->setContent($form->getHTML());
    }

}
