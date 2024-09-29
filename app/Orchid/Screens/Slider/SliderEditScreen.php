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
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SliderEditScreen extends Screen
{
    /**
     * @var Slider
     */
    public $slider;

    /**
     * Query to fetch the data for the slider.
     */
    public function query(Slider $slider): iterable
    {
        return [
            'slider' => $slider,
        ];
    }

    /**
     * The screen's name.
     */
    public function name(): ?string
    {
        return $this->slider->exists ? 'Edit Slider' : 'Create Slider';
    }

    /**
     * The screen's description.
     */
    public function description(): ?string
    {
        return 'Add or edit slider images, titles, and descriptions';
    }

    /**
     * Define the buttons in the screen's command bar.
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('bs.check-circle')
                ->method('save'),
            Button::make('Remove')
                ->icon('bs.trash3')
                ->confirm('Once the slider is deleted, it cannot be recovered.')
                ->method('remove')
                ->canSee($this->slider->exists),
        ];
    }

    /**
     * The layout for the screen.
     */
    public function layout(): iterable
    {
        return [
            Layout::rows([
                Picture::make('slider.image_path')
                    ->title('Slider Image')
                    ->required()
                    ->targetRelativeUrl(),

                Input::make('slider.title')
                    ->title('Title')
                    ->placeholder('Enter title')
                    ->help('Slider title'),

                Input::make('slider.description')
                    ->title('Description')
                    ->placeholder('Enter description')
                    ->help('Slider description'),
            ]),
        ];
    }

    /**
     * Handle the save request for the slider.
     */
    public function save(Slider $slider, Request $request)
    {
        $request->validate([
            'slider.image_path' => 'required',
            'slider.title' => 'required|string|max:255',
            'slider.description' => 'nullable|string',
        ]);

        $slider->fill($request->get('slider'))->save();

        Toast::info('Slider saved successfully.');

        return redirect()->route('platform.systems.sliders');
    }

    /**
     * Handle the remove request for the slider.
     */
    public function remove(Slider $slider)
    {
        $slider->delete();

        Toast::info('Slider removed successfully.');

        return redirect()->route('platform.systems.sliders');
    }
}
