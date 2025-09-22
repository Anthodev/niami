<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler\Report;

use App\Application\Command\Report\CreateReportCommentCommand;
use App\Application\CommandHandler\Report\CreateReportCommentCommandHandler;
use App\Application\Exception\Report\ReportNotFoundException;
use App\Application\Helper\MessageBusHelper;
use App\Application\Query\Report\GetReportByIdQuery;
use App\Domain\Model\Report\Report;
use App\Domain\Model\Report\ReportComment;
use App\Infrastructure\Persistence\Doctrine\Report\Repository\DoctrineReportCommentRepository;
use Faker\Factory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

beforeEach(function () {
    $this->faker = Factory::create();

    $this->reportCommentRepository = $this->createMock(DoctrineReportCommentRepository::class);
    $this->messageBus = $this->createMock(MessageBusInterface::class);
    $this->messageBusHelper = $this->createMock(MessageBusHelper::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->reportId = $this->faker->uuid();
    $this->comment = $this->faker->paragraph();
    $this->ip = $this->faker->ipv4();

    $this->report = $this->createMock(Report::class);
    $this->reportComment = $this->createMock(ReportComment::class);
});

it('successfully creates and saves report comment when report exists', function () {
    // Given
    $reportEnvelope = new Envelope($this->report, [new HandledStamp($this->report, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) {
            return $query instanceof GetReportByIdQuery
                && $query->reportId === $this->reportId;
        }))
        ->willReturn($reportEnvelope);

    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $reportEnvelope,
            'Report not found',
            Report::class,
        )
        ->willReturn($this->report);

    $this->reportCommentRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($reportComment) {
            return $reportComment instanceof ReportComment;
        }));

    $this->logger
        ->expects($this->never())
        ->method('error');

    $handler = new CreateReportCommentCommandHandler(
        $this->reportCommentRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger,
    );

    $command = new CreateReportCommentCommand(
        comment: $this->comment,
        ip: $this->ip,
        reportId: $this->reportId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('throws ReportNotFoundException when report is not found', function () {
    // Given
    $reportEnvelope = new Envelope($this->report, [new HandledStamp($this->report, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) {
            return $query instanceof GetReportByIdQuery
                && $query->reportId === $this->reportId;
        }))
        ->willReturn($reportEnvelope);

    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $reportEnvelope,
            'Report not found',
            Report::class
        )
        ->willReturn(null);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with(sprintf('Report %s not found', $this->reportId));

    $this->reportCommentRepository
        ->expects($this->never())
        ->method('save');

    $handler = new CreateReportCommentCommandHandler(
        $this->reportCommentRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger,
    );

    $command = new CreateReportCommentCommand(
        comment: $this->comment,
        ip: $this->ip,
        reportId: $this->reportId,
    );

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(ReportNotFoundException::class);
});

it('handles exception during save operation', function () {
    // Given
    $reportEnvelope = new Envelope($this->report, [new HandledStamp($this->report, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) {
            return $query instanceof GetReportByIdQuery
                && $query->reportId === $this->reportId;
        }))
        ->willReturn($reportEnvelope);

    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $reportEnvelope,
            'Report not found',
            Report::class
        )
        ->willReturn($this->report);

    $exception = new \Exception('Database connection failed');

    $this->reportCommentRepository
        ->expects($this->once())
        ->method('save')
        ->willThrowException($exception);

    $handler = new CreateReportCommentCommandHandler(
        $this->reportCommentRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger,
    );

    $command = new CreateReportCommentCommand(
        comment: $this->comment,
        ip: $this->ip,
        reportId: $this->reportId,
    );

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(\Exception::class);
});

it('creates report comment using ReportCommentFactory with correct parameters', function () {
    // Given
    $customComment = 'This is a test comment';
    $customIp = '192.168.1.100';
    $customReportId = 'test-report-id-123';

    $reportEnvelope = new Envelope($this->report, [new HandledStamp($this->report, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($customReportId) {
            return $query instanceof GetReportByIdQuery
                && $query->reportId === $customReportId;
        }))
        ->willReturn($reportEnvelope);

    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->with(
            $reportEnvelope,
            'Report not found',
            Report::class
        )
        ->willReturn($this->report);

    $this->reportCommentRepository
        ->expects($this->once())
        ->method('save')
        ->with($this->callback(function ($reportComment) {
            return $reportComment instanceof ReportComment;
        }));

    $handler = new CreateReportCommentCommandHandler(
        $this->reportCommentRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger
    );

    $command = new CreateReportCommentCommand(
        comment: $customComment,
        ip: $customIp,
        reportId: $customReportId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});

it('handles MessageBusHelper returning null for report', function () {
    // Given
    $reportEnvelope = new Envelope($this->report, [new HandledStamp($this->report, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->willReturn($reportEnvelope);

    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->willReturn(null);

    $this->logger
        ->expects($this->once())
        ->method('error')
        ->with(sprintf('Report %s not found', $this->reportId));

    $this->reportCommentRepository
        ->expects($this->never())
        ->method('save');

    $handler = new CreateReportCommentCommandHandler(
        $this->reportCommentRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger,
    );

    $command = new CreateReportCommentCommand(
        comment: $this->comment,
        ip: $this->ip,
        reportId: $this->reportId,
    );

    // When & Then
    expect(function () use ($handler, $command) {
        $handler->__invoke($command);
    })->toThrow(ReportNotFoundException::class);
});

it('properly dispatches GetReportByIdQuery with correct report ID', function () {
    // Given
    $specificReportId = 'specific-report-uuid-123';
    $reportEnvelope = new Envelope($this->report, [new HandledStamp($this->report, 'handler.service_id')]);

    $this->messageBus
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->callback(function ($query) use ($specificReportId) {
            return $query instanceof GetReportByIdQuery
                && $query->reportId === $specificReportId;
        }))
        ->willReturn($reportEnvelope);

    $this->messageBusHelper
        ->expects($this->once())
        ->method('getContentFromEnvelope')
        ->willReturn($this->report);

    $this->reportCommentRepository
        ->expects($this->once())
        ->method('save');

    $handler = new CreateReportCommentCommandHandler(
        $this->reportCommentRepository,
        $this->messageBus,
        $this->messageBusHelper,
        $this->logger,
    );

    $command = new CreateReportCommentCommand(
        comment: 'Test comment',
        ip: '127.0.0.1',
        reportId: $specificReportId,
    );

    // When
    $handler->__invoke($command);

    // Then
    expect(true)->toBeTrue();
});
