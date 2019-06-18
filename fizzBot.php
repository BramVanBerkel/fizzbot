<?php
require "vendor/autoload.php";
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use function GuzzleHttp\json_encode;

function getJson($url)
{
    $client = new Client();
    $response = $client->get($url);
    return (json_decode($response->getBody()->getContents()));
}

function sendAnswer($url, $answer)
{
    $client = new Client();
    $response = $client->post($url, [
        RequestOptions::JSON => $answer
    ]);
    return (json_decode($response->getBody()->getContents()));
}

function solveQuestion($rules, $numbers)
{
    $answer = '';
    foreach ($numbers as $number) {
        $res = '';
        foreach ($rules as $rule) {
            if (($number % $rule->number) === 0) {
                $res .= $rule->response;
            }
        }
        if (empty($res)) {
            $res = $number;
        }
        /**
         * avoid space on beginning of string
         * as an alternative we can trim() the answer on the end.
         */
        if (empty($answer)) {
            $answer .= $res;
        } else {
            $answer .= " $res";
        }
    }
    return $answer;
}

$base = 'https://api.noopschallenge.com';
$start = getJson($base . '/fizzbot');
$startPath = $start->nextQuestion;
echo($start->message . "\n");
$startQuestion = getJson($base . $startPath);
echo($startQuestion->message . "\n");
//Of course
$answer = ['answer' => 'PHP'];
echo("answer: " . json_encode($answer) . "\n");
$response = sendAnswer($base . $startPath, $answer);
while ($response->result === 'correct') {
    echo($response->message . "\n");
    $questionPath = $response->nextQuestion;
    $question = getJson($base . $questionPath);
    echo("Question rules: " . json_encode($question->rules) . "\n");
    echo("Question numbers: " . json_encode($question->numbers) . "\n");
    $answer = solveQuestion($question->rules, $question->numbers);
    echo("Answer: {$answer}\n");

    $response = sendAnswer($base . $questionPath, ['answer' => $answer]);
}
