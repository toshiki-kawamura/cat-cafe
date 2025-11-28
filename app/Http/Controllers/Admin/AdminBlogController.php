<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Models\Category;
use App\Models\Cat;
use App\Models\Blog;

class AdminBlogController extends Controller
{
    //ブログ一覧
    public function index()
    {
        //記事を10件取得し、ページネーションは前後ボタンのみ
        $blogs = Blog::latest('updated_at')->simplePaginate(10);
        return view('admin.blogs.index', ['blogs' => $blogs]);
    }

    //ブログ投稿画面
    public function create(){
        return view('admin.blogs.create');
    }

    // ブログ投稿処理
    public function store(StoreBlogRequest $request)
    {
        $validated = $request->validated();
        $validated['image'] = $request->file('image')->store('blogs', 'public');
        // $blog->image = $savedImagePath;
            // echo "<pre>";
            // var_dump($blog);
            // echo "</pre>";
            // exit;
        Blog::create($validated);

        return to_route('admin.blogs.index')->with('success', 'ブログを投稿しました。');
    }

    // 指定したIDのブログ編集画面
    public function edit(Blog $blog)
    {
        $categories = Category::all();
        $cats = Cat::all();
        return view('admin.blogs.edit', [
            'blog' => $blog,
            'categories' => $categories,
            'cats' => $cats
        ]);
    }

    // 指定したIDののブログ更新処理
    public function update(UpdateBlogRequest $request, string $id)
    {
        $blog = Blog::findOrFail($id);
        $updateData = $request->validated();

        //画像を変更する場合
        if($request->has('image')){

            //変更前の画像を削除
            Storage::disk('public')->delete($blog->image);

            //変更後の画像をアップロード、保存パスを更新対象データにセット
            $updateData['image'] = $request->file('image')->store('blogs', 'public');
        }
        $blog->category()->associate($updateData['category_id']);
        $blog->update($updateData);
        $blog->cats()->sync($updateData['cats'] ?? []);

        return to_route('admin.blogs.index')->with('success', 'ブログを更新した！');
    }
    
    //
    public function destroy(string $id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();
        Storage::disk('public')->delete($blog->image);

        return to_route('admin.blogs.index')->with('success', 'ブログを削除した！');
    }
}
