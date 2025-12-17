<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\InsuranceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InsuranceRequest;
use App\Models\Software;
use App\Repositories\InsuranceRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminInsuranceController extends Controller
{
    protected InsuranceRepository $insuranceRepository;

    public function __construct(InsuranceRepository $insuranceRepository)
    {
        $this->insuranceRepository = $insuranceRepository;
    }

    public function index(InsuranceDataTable $dataTable)
    {
        return $dataTable->render('admin.insurance.index');
    }

    public function create()
    {
        return view('admin.insurance.create');
    }

    public function store(InsuranceRequest $request)
    {
        $data = $request->all();

        $software = Software::where('name', config('constants.GLOBAL.SOFTWARE.DENTAL_4_WINDOWS'))->first();
        $data['software_id'] = $software?->id;
        $data['status'] = 'pending';

        $this->insuranceRepository->create($data);

        return redirect()->route('admin.insurance.index')->with('success', __('messages.message.insurance_create'));
    }

    public function edit($id)
    {
        try {
            $insurance = $this->insuranceRepository->findOrFail($id);

            return view('admin.insurance.edit', compact('insurance'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.insurance.index')->with('error', __('messages.message.insurance_not_found'));
        }
    }

    public function update(InsuranceRequest $request, $id)
    {
        try {
            $this->insuranceRepository->update($id, $request->all());

            return redirect()->route('admin.insurance.index')->with('success', __('messages.message.insurance_update'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.insurance.index')->with('error', __('messages.message.insurance_not_found'));
        }
    }

    public function destroy($id)
    {
        if (! $this->insuranceRepository->delete($id)) {
            return redirect()->route('admin.insurance.index')->with('error', __('messages.message.insurance_not_found'));
        }

        return redirect()->route('admin.insurance.index')->with('success', __('messages.message.insurance_delete'));
    }
}
