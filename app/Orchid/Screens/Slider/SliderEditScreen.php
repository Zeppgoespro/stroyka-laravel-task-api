<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Slider;

use App\Models\Slider;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SliderEditScreen extends Screen
{
    /**
     * @var Slider
     */
    public $slider;

    /**
     * Запрос для получения данных о слайдере.
     */
    public function query(Slider $slider): iterable
    {
        // Скорректировать путь изображения в зависимости от того, опубликован ли слайдер
        $slider->image_path = $slider->is_published
            ? url('/sliders/' . basename($slider->image_path))
            : $slider->image_path;

        return [
            'slider' => $slider,
        ];
    }

    /**
     * Название экрана.
     */
    public function name(): ?string
    {
        return $this->slider->exists ? 'Редактировать слайдер' : 'Создать слайдер';
    }

    /**
     * Описание экрана.
     */
    public function description(): ?string
    {
        return 'Добавление или редактирование слайдера';
    }

    /**
     * Определить кнопки на командной панели экрана.
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->icon('bs.check-circle')
                ->method('save'),
            Button::make('Удалить')
                ->icon('bs.trash3')
                ->confirm('После удаления слайдер не может быть восстановлен.')
                ->method('remove')
                ->canSee($this->slider->exists),
        ];
    }

    /**
     * Макет для экрана.
     */
    public function layout(): iterable
    {
        return [
            Layout::rows([
                Picture::make('slider.image_path')
                    ->title('Изображение слайдера')
                    ->required()
                    ->path('/sliders')
                    ->targetRelativeUrl(),

                Input::make('slider.title')
                    ->title('Название')
                    ->required()
                    ->placeholder('Введите название')
                    ->help('Название слайдера'),

                Input::make('slider.description')
                    ->title('Описание')
                    ->placeholder('Введите описание')
                    ->help('Описание слайдера'),
            ]),
        ];
    }

    public function save(Slider $slider, Request $request)
    {
        $request->validate([
            'slider.image_path' => 'required',
            'slider.title' => 'required|string|max:255',
            'slider.description' => 'nullable|string',
        ]);

        // Проверить, было ли изменено изображение
        if ($slider->exists && $slider->image_path !== $request->get('slider')['image_path']) {

            // Удалить старый файл изображения
            $oldImagePath = $slider->is_published
            ? base_path('/stroyka-main/static/sliders/' . basename($slider->image_path))
            : storage_path('app/public/' . str_replace('/storage/', '', $slider->image_path));

            unlink($oldImagePath);

            // Снять с публикации слайдер, если изображение изменено
            $slider->is_published = false;

            $slider->fill($request->get('slider'))->save();

            // Уведомить пользователя, что слайдер снят с публикации
            Alert::info('Слайдер был снят с публикации, так как изображение было изменено. Пожалуйста, опубликуйте его заново.');

            return redirect()->route('platform.systems.sliders');

        } else {
            // Сохранить новое изображение и другие поля
            $slider->fill($request->get('slider'))->save();

            // Уведомить пользователя, что слайдер успешно сохранен
            Toast::info('Слайдер успешно сохранен.');

            return redirect()->route('platform.systems.sliders');
        }
    }

    public function remove(Slider $slider)
    {
        if ($slider->is_published) {
            $imagePath = base_path('/stroyka-main/static/sliders/' . basename($slider->image_path));
        } else {
            $imagePath = storage_path('app/public/' . str_replace('/storage/', '', $slider->image_path));
        }

        if (file_exists($imagePath)) {
            unlink($imagePath); // Удалить файл изображения с диска
        }

        $slider->delete();

        Toast::info('Слайдер был успешно удален.');

        return redirect()->route('platform.systems.sliders');
    }
}
