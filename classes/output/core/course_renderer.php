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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace theme_clear\output\core;
defined('MOODLE_INTERNAL') || die();

use moodle_url;
use stdClass;
use html_writer;
use \theme_clear\external\clear_course_exporter;

require_once($CFG->dirroot . '/course/renderer.php');

/******************************************************************************************
 *
 * Overridden Core Course Renderer for the Clear theme
 *
 * @package    theme_clear
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

class course_renderer extends \core_course_renderer {

    /**
     * Returns HTML to print list of available courses for the frontpage
     *
     * @return string
     */
    public function frontpage_available_courses() {
        global $CFG;
        //require_once($CFG->libdir. '/coursecatlib.php');

        $chelper = new \coursecat_helper();
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
                set_courses_display_options(array(
                    'recursive' => true,
                    'limit' => $CFG->frontpagecourselimit,
                    'viewmoreurl' => new moodle_url('/course/index.php'),
                    'viewmoretext' => new \lang_string('fulllistofcourses')));

        $chelper->set_attributes(array('class' => 'frontpage-course-list-all'));
        $courses = \core_course_category::get(0)->get_courses($chelper->get_courses_display_options());
        $totalcount = \core_course_category::get(0)->get_courses_count($chelper->get_courses_display_options());
        if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', context_system::instance())) {
            // Print link to create a new course, for the 1st available category.
            return $this->add_new_course_button();
        }
        return $this->coursecat_course_cards($courses);
    }

    protected function coursecat_course_cards($courses) {
        global $CFG, $OUTPUT;

        $template = new stdClass;
        $template->courses = [];
        foreach ($courses as $course) {
            $course = get_course($course->id);
            $context = \context_course::instance($course->id);
            $exporter = new clear_course_exporter($course, ['context' => $context]);
            $template->courses[] = $exporter->export($OUTPUT);
        }

        return $this->render_from_template('theme_clear/coursecards', $template);
    }

}
