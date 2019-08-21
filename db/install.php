<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the plugin installation function.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The default file fields value.
 *
 * @var string
 */
define('DEFAULT_FILE_FIELDS',

        // Field location: HTML block > edition > content
        // Display location: HTML block
        // User: Any user
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "block_html|content|15\n" .

        // Field location: blog menu block > blog entry > edition > attachment
        // Display location: blog menu block > blog entry
        // User: Any user
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "blog|attachment|14\n" .

        // Field location: blog menu block > blog entry > edition > blog entry body
        // Display location: blog menu block > blog entry
        // User: Any user
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "blog|post|15\n" .

        // Field location: block calendar > date > new event > show more > description
        // Display location: block calendar > date
        // User: Any user
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "calendar|event_description|15\n" .

        // Field location: administration > users > accounts > cohorts > edition > description
        // Display location: administration > users > accounts > cohorts
        // User: Administrator
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "cohort|description|15\n" .

        // Prerequisites: Create a framework competency and a learning plan from administration > competencies
        // Field location: dashboard > learning plans > evidence of prior learning > add new evidence > files
        // Display location: dashboard > learning plans > evidence of prior learning
        // User: Administrator for the prerequisites and enrolled user for edition and display
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "core_competency|userevidence|14\n" .

        // Prerequisites: Authorize .mp4 files in administration > appearance > courses > course summary files
        //                extensions
        // Field location: course > edition > course summary files
        // Display location: category holding the course
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        // Accepted types: It depends on the configuration (courseoverviewfilesext)
        "course|overviewfiles|14\n" .

        // Field location: course > topic > edition > summary
        // Display location: course
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "course|section|15\n" .

        // Field location: course > course summary
        // Display location: category holding the course
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "course|summary|15\n" .

        // Field location: administration > courses > manage courses and catagories > category > edition >
        //                 description
        // Display location: category
        // User: Administrator
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "coursecat|description|15\n" .

        // Prerequisites: Enable outcomes in administration > advanced features > enable outcomes
        // Field location: course > outcomes > edit outcomes > add a new outcome > description
        // Display location: It doesn't seem to be displayed but it is added to outcomes CSV export
        // User: Administrator for prerequisites and course editor for edition and display
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "grade|outcome|15\n" .

        // Field location: administration > grades > scales > add a new scale > description
        // Display location: It doesn't seem to be displayed but it is added to outcomes CSV export
        // User: Administrator
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "grade|scale|15\n" .

        // Prerequisites: Set course grading method to "marking guide" in course > assignment module > edition >
        //                grade > grading method
        // Field location: course > assignment module > define marking guide > description
        // Display location: course > assignment module > advanced grading
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "grading|description|15\n" .

        // Field location: course > users > groups > create group > group description
        // Display location: course > users > groups > add/remove users
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "group|description|15\n" .

        // Field location: course > users > groups > groupings > create grouping > grouping description
        // Display location: course > users > groups > overview
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "grouping|description|15\n" .

        // Field location: course > assignment module > edition > description
        // Display location: course > assignment module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_assign|intro|15\n" .

        // Field location: course > assignment module > edition > additional files
        // Display location: course > assignment module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_assign|introattachment|14\n" .

        // Deprecated.
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_assignment|intro|15\n" .

        // Field location: course > book module > chapter > edition > content
        // Display location: course > book module and course > book module > chapter > print this chapter
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_book|chapter|15\n" .

        // Field location: course > book module > edition > description
        // Display location: course > book module > print book
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_book|intro|15\n" .

        // Field location: course > chat module > edition > description
        // Display location: course > chat module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_chat|intro|15\n" .

        // Field location: course > choice module > edition > description
        // Display location: course > choice module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_choice|intro|15\n" .

        // Field location: course > database module > edition > description
        // Display location: course > database module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_data|intro|15\n" .

        // Field location: course > feedback module > edition > description
        // Display location: course > feedback module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_feedback|intro|15\n" .

        // Field location: course > feedback module > edit questions > add question label > contents
        // Display location: course > feedback module > edit questions
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_feedback|item|15\n" .

        // Field location: course > feedback module > edition > after submission > completion message
        // Display location: course > feedback module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_feedback|page_after_submit|15\n" .

        // Field location: course > folder module > edition > files
        // Display location: course > folder module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_folder|content|14\n" .

        // Field location: course > folder module > edition > description
        // Display location: course > folder module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_folder|intro|15\n" .

        // Field location: course > forum module > edition > description
        // Display location: course > forum module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_forum|intro|15\n" .

        // Field location: course > glossary module > add new entry > attachment
        // Display location: course > glossary module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_glossary|attachment|14\n" .

        // Field location: course > glossary module > add new entry > definition
        // Display location: course > glossary module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_glossary|entry|15\n" .

        // Field location: course > glossary module > edition > description
        // Display location: course > glossary module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_glossary|intro|15\n" .

        // Field location: course > imscp module > edition > description
        // Display location: It doesn't seem to be displayed
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_imscp|intro|15\n" .

        // Field location: course > label module > edition > label text
        // Display location: course
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_label|intro|15\n" .

        // Field location: course > lesson module > grade essays > click on the date of an essay > your comments
        // Display location: It doesn't seem to be displayed
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_lesson|essay_responses|15\n" .

        // Field location: course > lesson module > edition > description
        // Display location: It doesn't seem to be displayed
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_lesson|intro|15\n" .

        // Field location: course > lesson module > edition > appearance > show more > linked media
        // Display location: course > lesson module > linked media block > click here to view
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_lesson|mediafile|14\n" .

        // Field location: course > lesson module > edit > add a question page here > matching > matching pair 1 >
        //                 answer
        // Display location: course > lesson module > edit
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_lesson|page_answers|15\n" .

        // Field location: course > lesson module > edit > add a question page here > essay > page contents
        // Display location: course > lesson module > edit
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_lesson|page_contents|15\n" .

        // Field location: course > lesson module > edit > add a question page here > short answer > answer 1 >
        //                 response
        // Display location: course > lesson module > edit
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_lesson|page_responses|15\n" .

        // Field location: course > external tool module > edition > show more > activity description and check
        //                 "display description on course page"
        // Display location: course
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_lti|intro|15\n" .

        // Field location: course > page module > edition > page content
        // Display location: course > page module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_page|content|15\n" .

        // Field location: course > page module > edition > description
        // Display location: course
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_page|intro|15\n" .

        // Field location: course > quiz module > edition > description
        // Display location: course > quiz module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_quiz|intro|15\n" .

        // Field location: course > quiz module > edition > overall feedback > feedback
        // Display location: course > quiz module and submit quiz
        // User: Course editor for edition and enrolled user for display
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_quiz|feedback|15\n" .

        // Field location: course > file module > edition > select files
        // Display location: course > file module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_resource|content|14\n" .

        // Field location: course > file module > edition > description
        // Display location: course > file module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_resource|intro|15\n" .

        // Field location: course > scorm module > edition > description
        // Display location: course > scorm module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_scorm|intro|15\n" .

        // Field location: course > survey module > edition > description
        // Display location: course > survey module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_survey|intro|15\n" .

        // Field location: course > URL module > edition > description
        // Display location: course > URL module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_url|intro|15\n" .

        // Field location: course > wiki module > edit > HTML format
        // Display location: course > wiki module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_wiki|attachments|14\n" .

        // Field location: course > wiki module > edition > description
        // Display location: course > wiki module
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_wiki|intro|15\n" .

        // Field location: course > workshop module > edition > feedback > conclusion
        // Display location: course > workshop module > close phase
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_workshop|conclusion|15\n" .

        // Field location: course > workshop module > edition > submission settings > instructions for submission
        // Display location: course > workshop module > submission phase
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_workshop|instructauthors|15\n" .

        // Field location: course > workshop module > edition > assessment settings > instructions for assessment
        // Display location: course > workshop module > assessment phase
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_workshop|instructreviewers|15\n" .

        // Field location: course > workshop module > edition > description
        // Display location: course > workshop module > setup phase
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "mod_workshop|intro|15\n" .

        // Field location: course > quiz module > question bank > edition > general feedback
        // Display location: course > quiz module and submit quiz
        // User: Course editor for edition and enrolled user for display
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "question|generalfeedback|15\n" .

        // Field location: course > quiz module > question bank > edition > question text
        // Display location: course > quiz module and submit quiz
        // User: Course editor for edition and enrolled user for display
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "question|questiontext|15\n" .

        // Field location: administration > appearance > manage tags > tag collection > collection > edition >
        //                 description
        // Display location: administration > appearance > manage tags > tag collection > collection
        // User: Administrator
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "tag|description|15\n" .

        // Field location: user > private files > files
        // Display location: private files block
        // User: Any user
        // Supported methods: FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "user|private|14\n" .

        // Field location: user > profile > edition > description
        // Display location: user > profile
        // User: Any user
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "user|profile|15\n" .

        // Prerequisites: Choose "accumulative grading" in course > workshop module > edition > gradings settings >
        //                grading strategy
        // Field location: course > workshop module > edit assessment form > description
        // Display location: course > workshop module > assessment phase > assess
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "workshopform_accumulative|description|15\n" .

        // Prerequisites: Choose "comments" in course > workshop module > edition > gradings settings > grading
        //                strategy
        // Field location: course > workshop module > edit assessment form > description
        // Display location: course > workshop module > assessment phase > assess
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "workshopform_comments|description|15\n" .

        // Prerequisites: Choose "number of errors" in course > workshop module > edition > gradings settings >
        //                grading strategy
        // Field location: course > workshop module > edit assessment form > description
        // Display location: course > workshop module > assessment phase > assess
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "workshopform_numerrors|description|15\n" .

        // Prerequisites: Choose "rubric" in course > workshop module > edition > gradings settings > grading
        //                strategy
        // Field location: course > workshop module > edit assessment form > description
        // Display location: course > workshop module > assessment phase > assess
        // User: Course editor
        // Supported methods: FILE_INTERNAL|FILE_EXTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK
        "workshopform_rubric|description|15");

/**
 * Installs OpenVeo Migration Tool plugin.
 *
 * It sets the default settings.
 */
function xmldb_tool_openveo_migration_install() {
    set_config('filefields', DEFAULT_FILE_FIELDS, 'tool_openveo_migration');
    set_config('videotypestomigrate', '.mp4', 'tool_openveo_migration');
    set_config('statuspollingfrequency', 10, 'tool_openveo_migration');
    set_config('planningpagevideosnumber', 10, 'tool_openveo_migration');
    set_config('uploadcurltimeout', 3600, 'tool_openveo_migration');
    set_config('migratedcoursevideonameformat', '%courseid% - %filename%', 'tool_openveo_migration');
    set_config('migratedmodulevideonameformat', '%moduleid% - %filename%', 'tool_openveo_migration');
    set_config('migratedcategoryvideonameformat', '%categoryid% - %filename%', 'tool_openveo_migration');
    set_config('migratedblockvideonameformat', '%blockid% - %filename%', 'tool_openveo_migration');
    set_config('migrateduservideonameformat', '%userid% - %filename%', 'tool_openveo_migration');
}
