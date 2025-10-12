<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPlanController
{
    public function index(Request $request)
    {
        $query = Plan::query();

        if ($request->filled('search') && ! empty(trim($request->search))) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->active !== null && $request->active !== 'all') {
            $query->where('is_active', $request->active);
        }

        if ($request->legacy !== null && $request->legacy !== 'all') {
            $query->where('is_legacy', $request->legacy);
        }

        $plans = $query->ordered()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans,
            'filters' => $request->only(['search', 'type', 'active', 'legacy']),
            'planTypes' => ['free', 'subscription', 'lifetime'],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Plans/Create', [
            'planTypes' => ['free', 'subscription', 'lifetime'],
        ]);
    }

    public function store(StorePlanRequest $request)
    {
        Plan::create($request->validated());

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function show(Plan $plan)
    {
        $plan->load(['subscriptions', 'trials']);

        return Inertia::render('Admin/Plans/Show', [
            'plan' => $plan,
        ]);
    }

    public function edit(Plan $plan)
    {
        return Inertia::render('Admin/Plans/Edit', [
            'plan' => $plan,
            'planTypes' => ['free', 'subscription', 'lifetime'],
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $plan->update($request->validated());

        return redirect()->back()->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        // Prevent deleting plans that have active subscriptions
        if ($plan->subscriptions()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete plan that has active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully.');
    }
}
