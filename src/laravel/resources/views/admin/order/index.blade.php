<x-app-layout>
    <x-content-header pageTitle="Заказы">
        <li class="breadcrumb-item"><a href="{{ route('main') }}">Главная</a></li>
        <li class="breadcrumb-item active">Заказы</li>
    </x-content-header>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 mb-3">

                    <div class="card">
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>
                                        ID
                                    </th>
                                    <th>Создан</th>
                                    <th>Доставка</th>
                                    <th>Стоимость</th>
                                    <th>Пользователь</th>
                                    <th>Статус</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}.</td>
                                        <td>{{ $order->created_at }}</td>
                                        <td>{{ $order->delivery }}</td>
                                        <td>{{ $order->total }} ₽</td>
                                        <td>
                                            {{ $order->user }}
                                        </td>
                                        <td>
                                            <select class="form-control order-status-select"
                                                    data-order-id="{{ $order->id }}">
                                                @foreach($statuses as $status)
                                                    <option
                                                        value="{{ $status->value }}"
                                                        @selected($status === $order->status)
                                                    >
                                                        {{ $status->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer clearfix">
                            <nav>
                                <ul class="pagination">
                                    {{-- Кнопка "Назад" --}}
                                    @if ($meta->prev_page_url)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $meta->prev_page_url }}"
                                               aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Номера страниц --}}
                                    @foreach ($meta->page_urls as $page => $url)
                                        <li class="page-item {{ $page === $meta->current_page ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endforeach

                                    {{-- Кнопка "Вперед" --}}
                                    @if ($meta->next_page_url)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $meta->next_page_url }}" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </nav>

                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const selects = document.querySelectorAll('.order-status-select');

                selects.forEach(select => {
                    select.addEventListener('change', function () {
                        const orderId = this.dataset.orderId;
                        const newStatus = this.value;

                        // Заблокировать select
                        this.disabled = true;
                        this.classList.remove('is-valid', 'is-invalid');

                        axios.patch(`/admin/orders/${orderId}/status`, {
                            status: newStatus
                        })
                            .then(response => {
                                // Успех — подсветить зелёным
                                this.classList.add('is-valid');
                            })
                            .catch(error => {
                                // Ошибка — подсветить красным и показать алерт
                                console.error('Ошибка при обновлении статуса', error);
                                this.classList.add('is-invalid');
                                alert('Ошибка при обновлении статуса');
                            })
                            .finally(() => {
                                // Разблокировать select и убрать стили через 2 секунды
                                setTimeout(() => {
                                    this.disabled = false;
                                    this.classList.remove('is-valid', 'is-invalid');
                                }, 2000);
                            });
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
