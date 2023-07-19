[![Minimum PHP Version](https://img.shields.io/badge/php->=8.0-8892BF.svg)](https://php.net/)

# SurveyDataGraphs

ILIAS plugin for display competency-based survey evaluations.

Following features are integrated:
* Competency Evaluation of single-choice questions surveys
* Graphic representation of individual competencies using single choice question surveys
  * Single Svy Evaluation (Bar-Chart)
  * Multi Svy Evaluation (Line-Chart)
* Table representation of individual competence levels including learning materials recommendation

## Requirements

### (* ILIAS 8.0 - 8.999)

#### (* PHP >=8.0)

## Installation

Start at your ILIAS root directory:

```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent/
cd Customizing/global/plugins/Services/COPage/PageComponent/
git clone https://github.com/kpgilias/SurveyDataGraphs.git
```

### Composer

After that, the composer dependencies need to be installed:

```bash
cd Customizing/global/plugins/Services/COPage/PageComponent/SurveyDataGraphs
composer install --no-dev
```
For development use '--no-dev' command
This step must also be done after an update.

Install, activate the plugin in the ILIAS Plugin Administration.

## Maintenance

Kröpelin Projekt Gmbh, support@kroepelin-projekte.de
This project is maintained by Kröpelin Projekt Gmbh (kpg), DE-Berlin (https://kroepelin-projekte.de). 
