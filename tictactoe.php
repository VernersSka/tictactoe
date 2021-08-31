<!DOCTYPE html>
<link rel="stylesheet" href="style.css">
<?php  

  include "navigation.php";

  $bot_begins = "bot_begins.txt";
  $bot_triggered = file_get_contents($bot_begins);

  if (array_key_exists('reset', $_GET) && $_GET['reset'] == 'true') {
    file_put_contents($bot_begins, "false");
    resetGame();
    $moves = [];
  } else {
    $moves = get();
  }

  if (array_key_exists('bot-first', $_GET)) {

    file_put_contents($bot_begins, "true");
    $bot_triggered = "true";

    $symbol = count($moves) % 2 == 0 ? 'x' : 'o';
    if (@$moves['winner'] === null && @$moves['draw'] === null) {
      bot_moves($moves, $symbol);
      checkWinner($symbol);
      checkDraw();
    }
  }

  if (array_key_exists('id', $_GET) && !array_key_exists($_GET['id'], $moves)) {
    $symbol = count($moves) % 2 == 0 ? 'x' : 'o';

    if (@$moves['winner'] === null && @$moves['draw'] === null) {
      add($_GET['id'], $symbol);
      checkWinner($symbol);
      checkDraw();
    }
    
    if (@$moves['winner'] === null && @$moves['draw'] === null) {
      bot_moves($moves, $symbol);
      $bot_symbol = $symbol == 'x' ? 'o' : 'x';
      checkWinner($bot_symbol);
      checkDraw();
    }
  }
?>

<div class="game_board">

  <?php 

  for ($i = 1; $i <= 9; $i++) {
    $symbol = array_key_exists($i, $moves) ? $moves[$i] : '';
    echo "<a href='?id=$i'>" . $symbol . "</a>";
  }

  ?>

</div>


<div class="game-options">
  
  <?php
    if ($bot_triggered == "false") {
      echo "<div class='bot-first-btn'>";
      echo "<a href=" . "?bot-first" . ">Bot move</a>";
      echo "</div>";
    }
  ?>

  <div class="reset-btn">
    <a href="?reset=true">RESET BOARD</a>
  </div>
</div>


<?php

function get() {
  if (!file_exists('tic_data.json')) {
    return [];
  }

  $content = file_get_contents('tic_data.json');
  $data = json_decode($content, true);
  if (!is_array($data)) {
    $data = [];
  }

  return $data;
}

function add($id, $symbol) {
  global $moves;
  if (!array_key_exists($id, $moves)) {
    $moves[$id] = $symbol;
    $json = json_encode($moves, JSON_PRETTY_PRINT);
    file_put_contents('tic_data.json', $json);
  }
}

function resetGame() {
  file_put_contents('tic_data.json', '{}');
  header('Location: ?');
}

function bot_moves($except, $symbol) {
  $random = mt_rand(1,9);
  $bot_symbol = count($except) % 2 == 0 ? 'x' : 'o';;

  if (!array_key_exists($random, $except)) {
    add($random, $bot_symbol);
    return;
  }

  bot_moves($except, $symbol);
}

function checkWinner($symbol) {
  global $moves;

  $win_combinations = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],

    [1, 4, 7],
    [2, 5, 8],
    [3, 6, 9],

    [1, 5, 9],
    [3, 5, 7],
  ];

  foreach ($win_combinations as $c) {
      if (
        @$moves[$c[0]] == $symbol &&
        @$moves[$c[1]] == $symbol &&
        @$moves[$c[2]] == $symbol
      ) {
        echo "<h2>Winner is '$symbol'!</h2>";
        add('winner', $symbol);
        return;
      }
    }
  }

  function checkDraw() {
    global $moves;
    if (count($moves) == 9 && (!array_key_exists('winner', $moves))) {
      echo "<h2>DRAW</h2>";
      add('draw', '');
      return;
    } 
  }

?>