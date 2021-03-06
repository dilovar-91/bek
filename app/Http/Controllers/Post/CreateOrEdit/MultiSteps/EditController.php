<?php
/**
 * LaraClassified - Classified Ads Web Application
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: https://bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from CodeCanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Post\CreateOrEdit\MultiSteps;

use App\Http\Controllers\Auth\Traits\VerificationTrait;
use App\Http\Controllers\Post\Traits\CustomFieldTrait;
use App\Http\Requests\PostRequest;
use App\Models\PostType;
use App\Models\Category;
use App\Models\Package;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\Post\CreateOrEdit\MultiSteps\Traits\EditTrait;

class EditController extends FrontController
{
	use EditTrait, VerificationTrait, CustomFieldTrait;
	
	public $data;
	public $msg = [];
	public $uri = [];
	
	/**
	 * EditController constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->commonQueries();
			
			return $next($request);
		});
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		// References
		$data = [];
		
		// Get Categories
		$data['categories'] = Category::where(function ($query) {
			$query->where('parent_id', 0)->orWhereNull('parent_id');
		})->with(['children'])->orderBy('lft')->get();
		view()->share('categories', $data['categories']);
		
		if (config('settings.single.show_post_types')) {
			// Get Post Types
			$data['postTypes'] = PostType::orderBy('lft')->get();
			view()->share('postTypes', $data['postTypes']);
		}
		
		// Count Packages
		$data['countPackages'] = Package::applyCurrency()->count();
		view()->share('countPackages', $data['countPackages']);
		
		// Count Payment Methods
		$data['countPaymentMethods'] = $this->countPaymentMethods;
		
		// Save common's data
		$this->data = $data;
	}
	
	/**
	 * Show the form the create a new ad post.
	 *
	 * @param $postId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function getForm($postId)
	{
		// Check if the form type is 'Single Step Form', and make redirection to it (permanently).
		if (config('settings.single.publication_form_type') == '2') {
			return redirect(url('edit/' . $postId), 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
		}
		
		return $this->getUpdateForm($postId);
	}
	
	/**
	 * Update ad post.
	 *
	 * @param $postId
	 * @param PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function postForm($postId, PostRequest $request)
	{
		return $this->postUpdateForm($postId, $request);
	}
}
