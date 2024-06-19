<?php
require_once __DIR__ . "/../vendor/autoload.php";

class ilSurveyDataGraphsPlugin extends ilPageComponentPlugin
{

    public function getPluginName(): string
    {
        return "SurveyDataGraphs";
    }

    public function getCssFiles($a_mode) :array
    {
        return array("css/surveydatagraphs.css");
    }

    public function getJavascriptFiles(string $a_mode) : array
    {
        return array("js/chart.min.js");
    }
    
    public function isValidParentType(string $a_type): bool
    {
        return true;
    }
}