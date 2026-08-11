@props(['message' => 'لا توجد بيانات حتى الآن.', 'colspan' => 5])

<tr>
    <td colspan="{{ $colspan }}" class="text-center text-muted-soft py-5">
        {{ $message }}
    </td>
</tr>
