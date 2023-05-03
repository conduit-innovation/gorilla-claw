<?php

require_once(__DIR__ . "/vendor/autoload.php");

define("NUM_CALLBACKS", 1);
define("NUM_RUNS", 1000);

class WP_Hook_Closure extends WP_Hook {

    public function apply_filters( $value, $args ) {
		if ( ! $this->callbacks ) {
			return $value;
		}

		$nesting_level = $this->nesting_level++;

		$this->iterations[ $nesting_level ] = array_keys( $this->callbacks );
		$num_args                           = count( $args );

		do {
			$this->current_priority[ $nesting_level ] = current( $this->iterations[ $nesting_level ] );
			$priority                                 = $this->current_priority[ $nesting_level ];

			foreach ( $this->callbacks[ $priority ] as $the_ ) {
				if ( ! $this->doing_action ) {
					$args[0] = $value;
				}

				// Avoid the array_slice() if possible.
				if ( 0 == $the_['accepted_args'] ) {
					$value = (\Closure::bind($the_['function'], null))();
				} elseif ( $the_['accepted_args'] >= $num_args ) {
					$value = (\Closure::bind($the_['function'], null))(...$args);
				} else {
					$value = $value = (\Closure::bind($the_['function'], null))(...(array_slice( $args, 0, (int) $the_['accepted_args'] )));
				}
			}
		} while ( false !== next( $this->iterations[ $nesting_level ] ) );

		unset( $this->iterations[ $nesting_level ] );
		unset( $this->current_priority[ $nesting_level ] );

		$this->nesting_level--;

		return $value;
	}

}

class WP_Hook_Callable_81 extends WP_Hook {

    public function apply_filters( $value, $args ) {
		if ( ! $this->callbacks ) {
			return $value;
		}

		$nesting_level = $this->nesting_level++;

		$this->iterations[ $nesting_level ] = array_keys( $this->callbacks );
		$num_args                           = count( $args );

		do {
			$this->current_priority[ $nesting_level ] = current( $this->iterations[ $nesting_level ] );
			$priority                                 = $this->current_priority[ $nesting_level ];

			foreach ( $this->callbacks[ $priority ] as $the_ ) {
				if ( ! $this->doing_action ) {
					$args[0] = $value;
				}

				// Avoid the array_slice() if possible.
				if ( 0 == $the_['accepted_args'] ) {
					$value = $the_['function']();
				} elseif ( $the_['accepted_args'] >= $num_args ) {
					$value = $the_['function']( ...$args );
				} else {
					$value = $the_['function']( ...(array_slice( $args, 0, (int) $the_['accepted_args'] )) );
				}
			}
		} while ( false !== next( $this->iterations[ $nesting_level ] ) );

		unset( $this->iterations[ $nesting_level ] );
		unset( $this->current_priority[ $nesting_level ] );

		$this->nesting_level--;

		return $value;
	}

}

echo "Comparing callable techniques...\n";

global $wp_filter;

$test_function = function($x) { return ++$x; }; 

$wp_filter['normal'] = new WP_Hook();
$wp_filter['closure'] = new WP_Hook_Closure();
$wp_filter['callable_81'] = new WP_Hook_Callable_81();

for($x = 0; $x < NUM_CALLBACKS; $x++) {
    //add_filter('normal', $test_function, $x, 1);
    $wp_filter['normal']->add_filter( 'normal', $test_function, $x, 1 );
    $wp_filter['closure']->add_filter( 'closure', $test_function, $x, 1 );
    $wp_filter['callable_81']->add_filter( 'callable_81', $test_function, $x, 1 );
}

$start = microtime(true);
for($x = 0; $x < NUM_RUNS; $x++) {

    prof_flag('normal');
    apply_filters('normal', 0);
    prof_flag('closure');
    apply_filters('closure', 0);
    prof_flag('callable_81');
    apply_filters('callable_81', 0);
    prof_flag('end');
    prof_collect();
}
$end = microtime(true);

prof_collect_print();

$duration = 1000 * ($end - $start);

echo("Run took ${duration}ms\n");

// Call this at each point of interest, passing a descriptive string
function prof_flag($str)
{
    global $prof_timing, $prof_names;
    $prof_timing[] = microtime(true);
    $prof_names[] = $str;
}

// Call this when you're done and want to see the results
function prof_print($prof_timing, $prof_names)
{
    $size = count($prof_timing);
    for($i=0;$i<$size - 1; $i++)
    {
        echo sprintf("{$prof_names[$i]}: %f\n", $prof_timing[$i+1]-$prof_timing[$i]);
    }
}

function prof_collect_print() {
    global $prof_collect;
    $dataset = [];

    foreach($prof_collect as $run) {

        foreach($run['names'] as $index => $name) {
            if(!isset($dataset[$name])) {
                $dataset[$name] = [];
            }

            if(($index + 1) < count($run['timing'])) {
                $dataset[$name][] = $run['timing'][$index + 1] - $run['timing'][$index];
            }
        }
    }

    foreach($dataset as $name => $times) {
        if(0 === count($times)) {
            continue;
        }

        $avg = 1000 * 1000 * (array_sum($times) / count($times));
        echo "${name}: ${avg}uS\n";
    }
}

function prof_collect()
{
    global $prof_timing, $prof_names, $prof_collect;
    $prof_collect[] = [
        'timing' => $prof_timing,
        'names' => $prof_names
    ];
    $prof_timing = [];
    $prof_names = [];
}