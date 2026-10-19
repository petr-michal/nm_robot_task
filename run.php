<?php
require __DIR__ . '/api.php';
const EMAIL = "petr@michalowie.cz";

function findCenterToEscape(array $steps): array {
    $length = $steps['length'];
    $height = $steps['height'];
    $x = $length / 2;
    $y = $height / 2;
    echo "Stred haly je na zde: ($x, $y)\n";
    return ['length' => $x, 'height' => $y];
}

try {
    if ($id = createRobot(EMAIL)) {

        echo "Robot created with ID: $id\n";

        $steps = [
            'up' => 0,
            'right' => 0,
            'height' => 0,
            'length' => 0,
            'totalCalls' => 0,
            'calls' => ['up' => 0, 'right' => 0, 'down' => 0, 'left' => 0],
        ];

        foreach (['up', 'right', 'down', 'left'] as $direction) {
            $goToWall = true;
            $moves = 0;
            while ($goToWall) {
                $result = moveRobot($id, $direction);

                echo "Pohybu: $direction, kroku: $result\n";

                $steps['totalCalls']++;
                $steps['calls'][$direction]++;

                if ($direction === 'up' || $direction === 'right') {
                    $steps[$direction] += $result;
                } else {
                    $steps[$direction == 'down' ? 'height' : 'length'] += $result;
                }
                $moves += $result;

                if ($result === 0) {
                    echo "Zed nalezena ve smeru: $direction\n";
                    echo "Počet kroků: {$moves}\n";
                    echo "Celkový počet volání: {$steps['totalCalls']}\n";
                    echo "==============================\n";
                    break;
                }
            }
        }

        echo "Delka: {$steps['height']}, Sirka: {$steps['length']}\n";
        echo "Pruzkum haly dokoncen.\n";

        $escapePoint = findCenterToEscape($steps);

        if($escapePoint === false) {
            echo "Nepovedlo se. :-(\n";
            exit(1);
        }

        $positionXStepsRest = 0;
        $positionYStepsRest = 0;

        if($escapePoint['length'] % 5 !== 0){
           $positionXStepsRest = $escapePoint['length'] % 5;
        }

        $positionXSteps = $escapePoint['length'] - $positionXStepsRest;

        if($escapePoint['height'] % 5 !== 0){
           $positionYStepsRest = $escapePoint['height'] % 5;
        }

        $positionYSteps = $escapePoint['height'] - $positionYStepsRest;

        echo "Pocet kroku pro pohyb vpravo: $positionXSteps, zbytek: $positionXStepsRest\n";
        echo "Pocet kroku pro pohyb nahoru: $positionYSteps, zbytek: $positionYStepsRest\n";

        foreach (['right', 'up'] as $direction) {
            $stepsToMove = $direction === 'up' ? $positionYSteps : $positionXSteps;
            $restToMove = $direction === 'up' ? $positionYStepsRest : $positionXStepsRest;

            while ($stepsToMove > 0) {
                $result = moveRobot($id, $direction);
                if(is_integer($result)) {
                    $stepsToMove -= $result;
                    echo "Robot v pohybu ve smeru: $direction, zbyva kroku: $stepsToMove\n";
                }
            }

            while ($restToMove > 0) {
                $result = moveRobot($id, $direction, $restToMove);
                if($result === $restToMove) {
                    echo "Posledni pohyb ve smeru: $direction, kroku: $restToMove.\n";
                    $restToMove = 0;
                }
            }
        }

        $result = escapeRobot($id);
        if($result) {
            echo "Je venku!\n";
            exit(0);
        }

        echo "Nepovedlo se!\n";

    } else {
        echo "Chyba robot nevytvořen.\n";
    }
} catch
(Exception $e) {
    echo "Error: ".$e->getMessage()."\n";
}

