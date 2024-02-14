<?php

/*
 * Label helper class for CPM libraries.
 */

namespace Custom\Libraries\CPM\v1;

use \RightNow\CPM\v1 as RNCPM;

class Labels {

    const ActionCreate = RNCPM\ActionCreate,
            ActionUpdate = RNCPM\ActionUpdate,
            ActionDestroy = RNCPM\ActionDestroy;

    protected static $actions = array(
        RNCPM\ActionCreate => "Create",
        RNCPM\ActionUpdate => "Update",
        RNCPM\ActionDestroy => "Destroy"
    );

    const RunModeLive = RNCPM\RunModeLive,
            RunModeTestHarness = RNCPM\RunModeTestHarness,
            RunModeTestObject = RNCPM\RunModeTestObject;

    protected static $runModes = array(
        RNCPM\RunModeLive => "Live",
        RNCPM\RunModeTestHarness => "Test Harness",
        RNCPM\RunModeTestObject => "Test Object"
    );

    /**
     * Returns the string representation, given the CPM object action constant.
     * Example)
     *   Labels::Action(\RightNow\CPM\v1\ActionCreate)
     *     returns "Create"
     * @param int $action
     * @return string | mixed
     */
    static function Action($action) {
        if (isset(self::$actions[$action])) {
            return self::$actions[$action];
        }
        return $action;
    }

    /**
     * Returns the string representation, given the CPM object run mode constant.
     * Example)
     *   Labels::Action(\RightNow\CPM\v1\RunModeTestHarness)
     *     returns "Test Harness"
     * @param int $runMode
     * @return string | mixed
     */
    static function RunMode($runMode) {
        if (isset(self::$runModes[$runMode])) {
            return self::$runModes[$runMode];
        }
        return $runMode;
    }

}
