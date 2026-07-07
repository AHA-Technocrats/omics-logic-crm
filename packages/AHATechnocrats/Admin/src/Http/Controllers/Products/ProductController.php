<?php

namespace AHATechnocrats\Admin\Http\Controllers\Products;

use AHATechnocrats\Admin\DataGrids\Product\ProductDataGrid;
use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Admin\Http\Requests\AttributeForm;
use AHATechnocrats\Admin\Http\Requests\MassDestroyRequest;
use AHATechnocrats\Admin\Http\Resources\ProductResource;
use AHATechnocrats\Product\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository)
    {
        request()->request->add(['entity_type' => 'products']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(ProductDataGrid::class)->process();
        }

        return view('admin::campaigns.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::campaigns.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttributeForm $request)
    {
        Event::dispatch('product.create.before');

        $product = $this->productRepository->create($this->campaignPayload($request));

        $this->syncCampaignAliases($product->id, $request->input('aliases'));

        Event::dispatch('product.create.after', $product);

        if (request()->ajax()) {
            return response()->json([
                'data' => $product,
                'message' => trans('admin::app.campaigns.index.create-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.campaigns.index.create-success'));

        return redirect()->route('admin.campaigns.index');
    }

    /**
     * Show the form for viewing the specified resource.
     */
    public function view(int $id): View
    {
        $product = $this->productRepository->findOrFail($id);

        return view('admin::campaigns.view', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        $inventories = $product->inventories()
            ->with('location')
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'name' => $inventory->location->name,
                    'warehouse_id' => $inventory->warehouse_id,
                    'warehouse_location_id' => $inventory->warehouse_location_id,
                    'in_stock' => $inventory->in_stock,
                    'allocated' => $inventory->allocated,
                ];
            });

        return view('admin::campaigns.edit', compact('product', 'inventories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttributeForm $request, int $id)
    {
        Event::dispatch('product.update.before', $id);

        $product = $this->productRepository->update($this->campaignPayload($request), $id);

        $this->syncCampaignAliases($id, $request->input('aliases'));

        Event::dispatch('product.update.after', $product);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.campaigns.index.update-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.campaigns.index.update-success'));

        return redirect()->route('admin.campaigns.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeInventories(int $id, ?int $warehouseId = null): JsonResponse
    {
        $this->validate(request(), [
            'inventories' => 'array',
            'inventories.*.warehouse_location_id' => 'required',
            'inventories.*.warehouse_id' => 'required',
            'inventories.*.in_stock' => 'required|integer|min:0',
            'inventories.*.allocated' => 'required|integer|min:0',
        ]);

        $product = $this->productRepository->findOrFail($id);

        Event::dispatch('product.update.before', $id);

        $this->productRepository->saveInventories(request()->all(), $id, $warehouseId);

        Event::dispatch('product.update.after', $product);

        return new JsonResponse([
            'message' => trans('admin::app.campaigns.index.update-success'),
        ], 200);
    }

    /**
     * Search product results
     */
    public function search(): JsonResource
    {
        $query = $this->productRepository
            ->pushCriteria(app(RequestCriteria::class))
            ->orderBy('created_at', 'desc');

        $excludedIds = request()->input('exclude_ids', []);

        if (is_string($excludedIds)) {
            $excludedIds = array_filter(array_map('trim', explode(',', $excludedIds)));
        }

        if (! empty($excludedIds)) {
            $query->whereNotIn('products.id', $excludedIds);
        }

        $products = $query->get();

        return ProductResource::collection($products);
    }

    /**
     * Returns product inventories grouped by warehouse.
     */
    public function warehouses(int $id): JsonResponse
    {
        $warehouses = $this->productRepository->getInventoriesGroupedByWarehouse($id);

        return response()->json(array_values($warehouses));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        try {
            Event::dispatch('settings.products.delete.before', $id);

            $product->delete($id);

            Event::dispatch('settings.products.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.campaigns.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => trans('admin::app.campaigns.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Mass Delete the specified resources.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('product.delete.before', $index);

            $this->productRepository->delete($index);

            Event::dispatch('product.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('admin::app.campaigns.index.delete-success'),
        ]);
    }

    /**
     * Merge campaign OmicsLogic fields into the attribute payload.
     */
    private function campaignPayload(AttributeForm $request): array
    {
        return array_merge($request->all(), [
            'category' => $request->input('category'),
            'mapping_status' => $request->input('mapping_status', 'mapped'),
            'is_active' => $request->boolean('is_active'),
        ]);
    }

    /**
     * Sync campaign aliases in the database.
     */
    private function syncCampaignAliases(int $productId, ?string $aliasesString): void
    {
        if (is_null($aliasesString) || trim($aliasesString) === '') {
            \DB::table('omics_product_aliases')->where('product_id', $productId)->delete();

            return;
        }

        $aliases = array_filter(array_map('trim', explode(',', $aliasesString)));

        // Remove aliases that are no longer present
        \DB::table('omics_product_aliases')
            ->where('product_id', $productId)
            ->whereNotIn('alias_name', $aliases)
            ->delete();

        // Insert new aliases
        foreach ($aliases as $alias) {
            \DB::table('omics_product_aliases')->updateOrInsert(
                ['product_id' => $productId, 'alias_name' => $alias],
                ['source' => 'manual', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
