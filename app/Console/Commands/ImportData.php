<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Topic;
use App\Answer;
use App\Question;
use App\Services\LanguageService;
use App\States\Answer\Translated as TranslatedAnswer;
use App\States\Question\Translated as TranslatedQuestion;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sherpa:import-data {language} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import question and answer data from a .csv file.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(LanguageService $languageService)
    {
        $counts = [
            'answers' => 0,
            'questions' => 0,
        ];
        $data = [];
        $handle = fopen($this->argument('file'), 'r');
        
        $topic = NULL;
        while( ($row = fgetcsv($handle)) !== FALSE) {
            if ($row[0]) {
                $topic = trim($row[0]);
            }

            $tmp = [
                'topic' => $topic,
                'answer' => trim($row[2]),
                'questions' => [trim($row[1])],
            ];

            foreach (array_slice($row, 3) as $question) {
                $question = trim($question);

                if ($question) {
                    $tmp['questions'][] = $question;
                }
            }

            $data[] = $tmp;
        }

        fclose($handle);

        $languageId = $languageService->getLanguageIdByCode($this->argument('language'));

        $topics = Topic::all()->keyBy('description');

        foreach ($data as $row) {
            $topic = $topics->get($row['topic']);

            if ($topic) {
                $row['topic_id'] = $topic->id;
            }

            $answer = new Answer;
            $answer->save();
            $answer->languages()->attach([
                $languageId => [
                    'description' => $row['answer'],
                ],
            ]);
            $answer->status->transitionTo(TranslatedAnswer::class);
            $counts['answers']++;

            foreach ($row['questions'] as $text) {
                $question = new Question;
                $question->save();

                if ($topic) {
                    $question->topic()->associate($topic->id)->save();
                }

                $question->answer()->associate($answer->id)->save();
                $question->languages()->attach([
                    $languageId => [
                        'description' => $text,
                    ],
                ]);
                $question->status->transitionTo(TranslatedQuestion::class);
                $counts['questions']++;
            }
        }

        $this->info("Imported {$counts['questions']} questions and {$counts['answers']} answers.");
    }
}
