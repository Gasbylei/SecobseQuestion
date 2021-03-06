<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Post;
use App\Repositories\QuestionRepository;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

use Auth;
use App\Question;
use Illuminate\Support\Facades\DB;
use GrahamCampbell\Markdown\Facades\Markdown;

class QuestionController extends Controller
{
    private $questionRepository;
	/**
	 * Instantiate QuestionController instance.
	 *
	 * @return void
	 */
	public function __construct(QuestionRepository $questionRepository)
	{
		$this->middleware('auth')->except('show', 'index','search');
		$this->questionRepository = $questionRepository;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		return view('questions.createQuestion');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Reque   st $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Requests\StoreQuestionRequest $request)
	{
	    $tags = $this->questionRepository->normalizeTag($request->get('tags'));
		$data = [
            'title' => $request->get('title'),
            'content' => $request->get('mdContent'),
            'username' => Auth::user()->name,
        ];

		$question = $this->questionRepository->create($data);

		$question->tags()->attach($tags);
        $user = User::find(Auth::user()->id)->increment('questions_count');

		flash('提问成功!', 'success');

		return redirect()->route('questions.show', [$question->id]);
	}

	/**
	 * Show single question and author.
	 *
	 * @param  int $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		$question =  $this->questionRepository->byIdWithTags($id);

		$question->readtimes += 1;

		$userAvatar = DB::table('users')->where('name',$question->username)->value('avatar');

		$answer = Answer::all()->where('question_id',$id)->sortByDesc('isadopt');

		$question->save();

		$question->content = Markdown::convertToHtml($question->content);

		$popularQuestions = $question->orderBy('answertimes','desc')->where('answertimes','>',0)->limit(5)->get();
		return view('questions.show', compact('question','userAvatar','answer','popularQuestions'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$question =  $this->questionRepository->byIdWithTags($id);

		if (Auth::user()->owns($question)) {
            return view('questions.edit', compact('question'));
        }

		return back();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Requests\StoreQuestionRequest $request, $id)
	{
		$question = $this->questionRepository->byIdWithTags($id);
        $tags = $this->questionRepository->normalizeTag($request->get('tags'));

		$question->update([
            'title' => $request->get('title'),
            'content' => $request->get('mdContent')
        ]);

		$question->tags()->sync($tags);

        flash('问题更新成功!', 'success');

        return redirect()->route('questions.show', [$question->id]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		return $this->questionRepository->byIdDelete($id);
	}

    /**
     * Search questions and posts
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $q = $request->get('q');

        $questions = Question::search($q, null, true)->paginate(20);

        $posts = Post::search($q, null, true)->paginate(20);

        $question_count = $questions->count('id');
        $post_count = $posts->count('id');

        $count = $question_count + $post_count;

        return view('questions.search', compact('questions', 'posts', 'q','count','question_count','post_count'));
    }
}
