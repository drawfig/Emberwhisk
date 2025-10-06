<?php
namespace Middleware;

spl_autoload_register(function ($class_name) {
    if(file_exists(__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php")) {
        require_once (__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php");
    }
});

spl_autoload_register(function ($class_name) {
    include ($class_name . ".php");
});
class Middleware_Manager {
    public function run($data, $fd, $server, $run_type) {
        $region_middleware_list = $this->get_region_middleware($data['message_type'], $server, $fd);
        $local_middleware_list = $this->ready_run_list_from_local($data['message_type']);
        return $this->run_middleware($data, $server, $fd, $this->compile_final_middleware_list($region_middleware_list, $local_middleware_list, $data['message_type']), $run_type);
    }

    private function compile_final_middleware_list($region_middleware_list, $local_middleware_list, $route) {
        $middleware_software = new Middleware_Software_Groups();
        $middleware_groups = new Middleware_Routing_Groups();
        $output = [];
        foreach ($middleware_software->GLOBAL_MIDDLEWARE as $middleware) {
            if (!in_array($route, $middleware_groups->GLOBAL_BYPASS_ROUTES)) {
                $output[] = $middleware;
            }
        }
        foreach ($region_middleware_list as $middleware) {
            if (!in_array($middleware, $output)) {
                $output[] = $middleware;
            }
        }
        foreach ($local_middleware_list as $middleware) {
            if (!in_array($middleware, $output)) {
                $output[] = $middleware;
            }
        }
        return $output;
    }


    private function get_region_middleware($data, $server, $fd) {
        $region_out = [];
        $middleware_groups = new Middleware_Routing_Groups();
        $middleware_software = new Middleware_Software_Groups();

        foreach ($middleware_software->REGIONAL_MIDDLEWARE as $region => $middleware_list) {
            $route_list = $middleware_groups->get_region($region);
            if(in_array($data, $route_list)) {
                $region_out[] = $region;
            }
        }

        return $this->ready_run_list_from_region($region_out);

    }

    private function get_local_middleware($group_name) {
        $middleware_software = new Middleware_Software_Groups();
        $output = [];
        foreach ($middleware_software->LOCAL_GROUP_MIDDLEWARE[$group_name] as $middleware) {
            $output[] = $middleware;
        }

        return $output;
    }

    private function ready_run_list_from_local($route) {
        $middleware_group = new Middleware_Routing_Groups();
        $group_list = [];
        foreach ($middleware_group->LOCAL_GROUPS as $group => $middleware_list) {
            if(in_array($route, $middleware_list)) {
                $group_list[] = $group;
            }
        }

        $output = [];
        foreach ($group_list as $group) {
            $container = $this->get_local_middleware($group);
            foreach ($container as $middleware) {
                if (!in_array($middleware, $output)) {
                    $output[] = $middleware;
                }
            }
        }
        return $output;
    }

    private function ready_run_list_from_region($region_list) {
        $middleware_software = new Middleware_Software_Groups();
        $output = [];
        foreach ($region_list as $region) {
            $region_middleware_list = $middleware_software->REGIONAL_MIDDLEWARE[$region];
            foreach ($region_middleware_list as $middleware_entry) {
                $raw_out = explode(":", $middleware_entry);
                if (sizeof($raw_out) == 2 && $raw_out[0] == "GROUP") {
                    $out = $this->get_local_middleware($raw_out[1]);
                    foreach ($out as $middleware) {
                        if (!in_array($middleware, $output)) {
                            $output[] = $middleware;
                        }
                    }
                } else {
                    if (!in_array($middleware_entry, $output)) {
                        $output[] = $middleware_entry;
                    }
                }
            }
        }
        return $output;
    }

    private function run_middleware($data, $server, $fd, $middleware_list, $run_type) {
        foreach ($middleware_list as $middleware) {
            $middleware_class = "\\Middleware\\{$middleware}";
            $middleware_instance = new $middleware_class($run_type);
            $check = $middleware_instance->run($data, $server, $fd);
            if($check === false) {
                return false;
            }
        }
        return true;
    }

}