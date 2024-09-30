<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Slider;

use App\Models\Slider;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class SliderScreen extends Screen
{
    /**
     * Запрос для получения списка слайдеров.
     */
    public function query(): iterable
    {
        return [
            'sliders' => Slider::paginate(),
        ];
    }

    /**
     * Название экрана.
     */
    public function name(): ?string
    {
        return 'Управление слайдерами';
    }

    /**
     * Описание экрана.
     */
    public function description(): ?string
    {
        return 'Управление изображениями слайдера, отображаемыми на главной странице';
    }

    /**
     * Командная панель экрана с кнопкой "Добавить".
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Добавить слайдер')
                ->icon('bs.plus-circle')
                ->route('platform.systems.sliders.create'),
        ];
    }

    /**
     * Макет для отображения списка слайдеров.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('sliders', [
                TD::make('id', 'ID'),

                TD::make('image_path', 'Изображение')
                ->render(function(Slider $slider) {
                    $imagePath = $slider->is_published
                        ? url('/sliders/' . basename($slider->image_path)) // Маршрут для опубликованных изображений слайдера
                        : url($slider->image_path); // Неопубликованный слайдер (изображение из хранилища)

                    return "<img src='{$imagePath}' style='width: 150px;' />";
                }),

                TD::make('title', 'Название'),

                TD::make('description', 'Описание'),

                TD::make('Действия')
                    ->render(function(Slider $slider) {
                        $editButton = Link::make('Редактировать')->route('platform.systems.sliders.edit', $slider);

                        if (!$slider->is_published) {
                            $publishButton = Button::make('Опубликовать')
                                ->method('publish')
                                ->confirm('Опубликовать этот слайдер?')
                                ->parameters(['id' => $slider->id]);
                            return $editButton . ' ' . $publishButton;
                        } else if ($slider->is_published) {
                            $unpublishButton = Button::make('Снять с публикации')
                            ->method('unpublish')
                            ->confirm('Снять этот слайдер с публикации?')
                            ->parameters(['id' => $slider->id]);
                        return $editButton . ' ' . $unpublishButton;
                        }

                        return $editButton;
                    }),
            ]),
        ];
    }

    public function publish(Request $request)
    {
        $slider = Slider::findOrFail($request->get('id'));

        // Текущий путь к изображению в хранилище
        $currentPath = storage_path('app/public/' . str_replace('/storage/', '', $slider->image_path));

        // Определить новый путь в проекте Nuxt
        $newPath = base_path('/stroyka-main/static/sliders/' . basename($slider->image_path));

        // Переместить изображение
        if (file_exists($currentPath)) {
            rename($currentPath, $newPath);

            // Обновить image_path в базе данных на новое местоположение
            $slider->image_path = '/static/sliders/' . basename($slider->image_path);
            $slider->is_published = true;
            $slider->save();

            Toast::info('Слайдер успешно опубликован.');
        } else {
            Toast::error('Файл изображения не найден.');
        }

        return redirect()->route('platform.systems.sliders');
    }

    public function unpublish(Request $request)
    {
        $slider = Slider::findOrFail($request->get('id'));

        // Текущий путь к изображению в Nuxt
        $currentPath = base_path('/stroyka-main/static/sliders/' . basename($slider->image_path));

        // Определить путь для возврата изображения в хранилище Laravel
        $newPath = storage_path('app/public/sliders/' . basename($slider->image_path));

        // Переместить изображение обратно
        if (file_exists($currentPath)) {
            rename($currentPath, $newPath);

            // Обновить image_path в базе данных на исходное местоположение
            $slider->image_path = '/storage/sliders/' . basename($slider->image_path);
            $slider->is_published = false;
            $slider->save();

            Toast::info('Слайдер успешно снят с публикации.');
        } else {
            Toast::error('Опубликованный файл изображения не найден.');
        }

        return redirect()->route('platform.systems.sliders');
    }

}
