<?php

namespace App\Http\Controllers;

use App\Http\Requests\GolfCourseRequest;
use App\Models\GolfCourse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GolfCourseController extends Controller
{
    private const IMAGE_FIELDS = ['image1', 'image2', 'image3'];
    private const STORAGE_DISK = 'public';

    /**
     * 一覧（検索 + ページネーション）
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q'          => ['nullable', 'string', 'max:100'],
            'prefecture' => ['nullable', 'string', 'max:255'],
            'locale'     => ['nullable', 'in:ja,en'],
            'kind'       => ['nullable', 'in:indoor,outdoor,short,long'],
        ]);

        $courses = GolfCourse::query()
            ->search($filters)
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());

        return view('golf_courses.index', [
            'courses' => $courses,
            'filters' => $filters,
        ]);
    }

    /**
     * 詳細
     */
    public function show(GolfCourse $golfCourse): View
    {
        return view('golf_courses.show', ['course' => $golfCourse]);
    }

    /**
     * 新規作成フォーム
     */
    public function create(): View
    {
        return view('golf_courses.create', [
            'course' => new GolfCourse([
                'locale'       => 'ja',
                'country_code' => 'JP',
            ]),
        ]);
    }

    /**
     * 登録
     */
    public function store(GolfCourseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        // 画像は別処理
        foreach (self::IMAGE_FIELDS as $field) {
            unset($data[$field], $data["remove_{$field}"]);
        }

        $course = GolfCourse::create($data);

        $this->handleImageUploads($request, $course);

        return redirect()
            ->route('golf-courses.index')
            ->with('status', '登録しました。');
    }

    /**
     * 編集フォーム
     */
    public function edit(GolfCourse $golfCourse): View
    {
        return view('golf_courses.edit', ['course' => $golfCourse]);
    }

    /**
     * 更新
     */
    public function update(GolfCourseRequest $request, GolfCourse $golfCourse): RedirectResponse
    {
        $data = $request->validated();
        foreach (self::IMAGE_FIELDS as $field) {
            unset($data[$field], $data["remove_{$field}"]);
        }

        $golfCourse->update($data);

        $this->handleImageUploads($request, $golfCourse);

        return redirect()
            ->route('golf-courses.index')
            ->with('status', '更新しました。');
    }

    /**
     * 削除確認画面
     */
    public function confirmDelete(GolfCourse $golfCourse): View
    {
        return view('golf_courses.delete', ['course' => $golfCourse]);
    }

    /**
     * 論理削除
     */
    public function destroy(GolfCourse $golfCourse): RedirectResponse
    {
        $golfCourse->delete();

        return redirect()
            ->route('golf-courses.index')
            ->with('status', '削除しました（復元可能）。');
    }

    /**
     * 削除済み一覧
     */
    public function trashed(): View
    {
        $courses = GolfCourse::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->paginate(20);

        return view('golf_courses.trashed', ['courses' => $courses]);
    }

    /**
     * 復元
     */
    public function restore(int $id): RedirectResponse
    {
        $course = GolfCourse::onlyTrashed()->findOrFail($id);
        $course->restore();

        return redirect()
            ->route('golf-courses.trashed')
            ->with('status', '復元しました。');
    }

    /**
     * 完全削除（画像も削除）
     */
    public function forceDestroy(int $id): RedirectResponse
    {
        $course = GolfCourse::onlyTrashed()->findOrFail($id);

        foreach (self::IMAGE_FIELDS as $field) {
            $this->deleteStoredImage($course->{$field});
        }

        $course->forceDelete();

        return redirect()
            ->route('golf-courses.trashed')
            ->with('status', '完全削除しました。');
    }

    /**
     * 画像3枚分の差し替え／削除処理
     */
    private function handleImageUploads(GolfCourseRequest $request, GolfCourse $course): void
    {
        foreach (self::IMAGE_FIELDS as $field) {
            $remove = $request->boolean("remove_{$field}");
            $file   = $request->file($field);

            if ($file instanceof UploadedFile) {
                // 新規アップロード：旧ファイル削除→新規保存
                $this->deleteStoredImage($course->{$field});
                $path = $file->store("golf_courses/{$course->id}", self::STORAGE_DISK);
                $course->{$field} = $path;
            } elseif ($remove) {
                // 削除チェック ON
                $this->deleteStoredImage($course->{$field});
                $course->{$field} = null;
            }
        }

        if ($course->isDirty(self::IMAGE_FIELDS)) {
            $course->save();
        }
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && Storage::disk(self::STORAGE_DISK)->exists($path)) {
            Storage::disk(self::STORAGE_DISK)->delete($path);
        }
    }
}
