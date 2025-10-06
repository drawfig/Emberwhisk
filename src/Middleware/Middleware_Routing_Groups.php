<?php
namespace Middleware;

class Middleware_Routing_Groups {
    public $REGIONAL_GROUPS =[
        "example_region" => ["GROUP:example_group"],
    ];

    public $LOCAL_GROUPS = [
        "example_group" => [
            "example_route",
            "example_route_2",
        ],
    ];

    public $GLOBAL_BYPASS_ROUTES = [
        "example_route",
    ];

    public function get_group($group) {
        if(array_key_exists($group, $this->LOCAL_GROUPS)) {
            return $this->LOCAL_GROUPS[$group];
        }
        else {
            return false;
        }
    }

    public function get_region($region) {
        $raw = $this->REGIONAL_GROUPS[$region];
        $output = [];
        foreach ($raw as $row) {
            $group = $this->group_check($row);
            if($group) {
                foreach ($group as $inner_row) {
                    $output[] = $inner_row;
                }
            }
            else {
                $output[] = $row;
            }
        }

        return $output;
    }

    private function group_check($group) {
        $group_chk = explode(":", $group);
        if(sizeof($group_chk) != 2) {
            return false;
        }

        if($group_chk[0] !== "GROUP") {
            return false;
        }

        if(array_key_exists($group_chk[1], $this->LOCAL_GROUPS)) {
            return $this->LOCAL_GROUPS[$group_chk[1]];
        }
        else {
            return false;
        }
    }

}