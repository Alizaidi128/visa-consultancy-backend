@props([
    'url',
    'align' => 'center',
])

<table
    align="{{ $align }}"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
    style="margin: 30px 0;"
>
    <tr>
        <td align="{{ $align }}">
            <a
                href="{{ $url }}"
                target="_blank"
                rel="noopener"
                style="
                    display: inline-block;
                    padding: 14px 28px;
                    font-size: 14px;
                    font-weight: 600;
                    color: #ffffff;
                    background-color: #2563eb; /* primary brand blue */
                    border-radius: 10px;
                    text-decoration: none;
                    letter-spacing: 0.3px;
                    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25);
                "
            >
                {!! $slot !!}
            </a>
        </td>
    </tr>
</table>
