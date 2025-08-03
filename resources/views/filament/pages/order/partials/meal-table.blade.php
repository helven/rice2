<table class="w-full text-sm">
    <thead>
        <tr class="bg-gray-50">
            <th class="py-2 px-2 text-left font-medium text-gray-700">Meal</th>
            <th class="py-2 px-2 text-center font-medium text-gray-700">Normal</th>
            <th class="py-2 px-2 text-center font-medium text-gray-700">Big</th>
            <th class="py-2 px-2 text-center font-medium text-gray-700">Small</th>
            <th class="py-2 px-2 text-center font-medium text-gray-700">S.Small</th>
            <th class="py-2 px-2 text-center font-medium text-gray-700">No Rice</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @foreach($meals as $meal)
            <tr>
                <td class="py-2 px-2">{{ $meal['name'] ?? $meal['meal_name'] ?? 'N/A' }}</td>
                <td class="py-2 px-2 text-center">{{ $meal['normal'] ?? 0 }}</td>
                <td class="py-2 px-2 text-center">{{ $meal['big'] ?? 0 }}</td>
                <td class="py-2 px-2 text-center">{{ $meal['small'] ?? 0 }}</td>
                <td class="py-2 px-2 text-center">{{ $meal['s_small'] ?? 0 }}</td>
                <td class="py-2 px-2 text-center">{{ $meal['no_rice'] ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="pt-2 text-sm text-gray-600">
                <div class="space-y-1">
                    <div class="text-right font-bold">Amount: RM{{ number_format($total_amount, 2) }}</div>
                    @if(isset($delivery_fee))
                        <div class="text-right font-bold text-blue-600">Delivery Fee: RM{{ number_format($delivery_fee, 2) }}</div>
                        <div class="text-right font-bold text-green-600 border-t pt-1">Total: RM{{ number_format($total_amount + $delivery_fee, 2) }}</div>
                    @endif
                    @if(!empty($notes))
                        <div class="font-medium">Notes:</div>
                        <div class="px-2 py-1 bg-gray-50 p-4 rounded-lg">{{ $notes }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </tfoot>
</table>