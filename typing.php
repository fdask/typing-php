#!/usr/bin/php
<?php
class Config {
    public static $initialPileCount = 10;
    public static $tickAt = 5000;

	public static function waitForInput() {
		$input = '';

		$read = [STDIN];
		$write = null;
		$except = null;

		readline_callback_handler_install('', function() {});

		// Read characters from the command line one at a time until there aren't any more to read
		do {
			$input .= fgetc(STDIN);
		} while (stream_select($read, $write, $except, 0, 1));

		readline_callback_handler_remove();

		return $input;
	}
}

class Typing {
    private $term;
    private $ticks;
    private $tickAt;
    private $pile;
    private $level;

    public function __construct() {
        $this->ticks = 0;
        $this->tickAt = Config::$tickAt;
        $this->pile = array();
        $this->level = 0;

        for ($x = 0; $x < Config::$initialPileCount; $x++) {
            $this->addChar();
        }

        $this->gameLoop();
    }

    public function tick() {
        $this->ticks++;
    }

    public function gameLoop() {
        $this->term = `stty -g`;
		system("stty -icanon -echo");

        stream_set_blocking(STDIN, false); // Do not wait

        $this->draw();

        // START OF INFINITE LOOP
        while (1) {
            $c = Config::waitForInput();

            switch ($c) {
                case $this->pile[0]:
                    // we typed the right key
                    array_shift($this->pile);            
                    
                    if (empty($this->pile)) {
                        $this->levelUp();
                    }

                    $this->draw();

                    break;
                case chr(27):
                    $this->gameOver();
                    break;
                default:
                    // do nothing
            }

            $this->tick();

            // START OF TICK
            if ($this->ticks >= $this->tickAt) {
                $this->ticks = 0;
    
                $this->addChar();

                $this->draw();
            }    
            // END OF TICK
        }
        // END OF INFINITE LOOP
    }

    public function levelUp() {
        $this->level++;
        
        for ($x = 0; $x < Config::$initialPileCount + $this->level; $x++) {
            $this->addChar();
        }

        $this->tickAt = $this->tickAt - 50;
    }

    public function addChar() {
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $arr = str_split($str);

        $this->pile[] = $arr[array_rand($arr)];
    }

    public function gameOver() {
		echo "GAME OVER\n\n";

		// quit the game
		system("stty " . $this->term);

		exit;
	}

    public function draw() {
        system("clear");

        echo implode("", $this->pile) . "\n";
    }
}

$t = new Typing();