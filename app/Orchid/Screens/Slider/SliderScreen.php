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
     * Query to fetch the list of sliders.
     */
    public function query(): iterable
    {
        return [
            'sliders' => Slider::paginate(),
        ];
    }

    /**
     * The name of the screen.
     */
    public function name(): ?string
    {
        return 'Slider Management';
    }

    /**
     * The screen's description.
     */
    public function description(): ?string
    {
        return 'Manage the slider images displayed on the main page';
    }

    /**
     * The screen's command bar with an "Add" button.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Add Slider')
                ->icon('bs.plus-circle')
                ->route('platform.systems.sliders.create'),
        ];
    }

    /**
     * The layout for displaying the list of sliders.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('sliders', [
                TD::make('id', 'ID'),

                TD::make('image_path', 'Image')
                    ->render(fn(Slider $slider) => "<img src='{$slider->image_path}' style='width: 150px;'/>"),

                TD::make('title', 'Title'),

                TD::make('description', 'Description'),

                TD::make('Actions')
                    ->render(function(Slider $slider) {
                        $editButton = Link::make('Edit')->route('platform.systems.sliders.edit', $slider);

                        if (!$slider->is_published) {
                            $publishButton = Button::make('Publish')
                                ->method('publish')
                                ->confirm('Publish this slider?')
                                ->parameters(['id' => $slider->id]);
                            return $editButton . ' ' . $publishButton;
                        }

                        return $editButton;
                    }),
            ]),
        ];
    }

    public function publish(Request $request)
    {
        $slider = Slider::findOrFail($request->get('id'));

        // Current image path in storage
        $currentPath = storage_path('app/public/' . str_replace('/storage/', '', $slider->image_path));

        // Define the new path in the Nuxt project
        $newPath = base_path('/stroyka-main/static/sliders/' . basename($slider->image_path));

        // Move the image
        if (file_exists($currentPath)) {
            rename($currentPath, $newPath);

            // Update the image_path in the database to the new location
            $slider->image_path = '/static/sliders/' . basename($slider->image_path);
            $slider->is_published = true;
            $slider->save();

            Toast::info('Slider published successfully.');
        } else {
            Toast::error('Image file not found.');
        }

        return redirect()->route('platform.systems.sliders');
    }

}
