<?php
namespace SimonHamp\LaravelNovaCsvImport;

use Illuminate\Support\Arr;
use Laravel\Nova\Http\Requests\NovaRequest;

class ImportRequest extends NovaRequest
{
    protected $data;

    public function __construct($data)
    {
        parent::__construct();

        $this->data = $data;
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string|null $key
     * @param  string|array|null $default
     * @return string|array|null
     */
    public function input($key = null, $default = null)
    {
        if (! $key) {
            return $this->data;
        }

        if (! isset($this->data[$key])) {
            return $default;
        }

        return $this->data[$key];
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return Arr::has(
            $this->input(),
            $offset
        );
    }
}
