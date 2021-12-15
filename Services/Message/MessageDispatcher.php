<?php
/*
* created on: 15/12/2021 - 15:35
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Message;

use App\Entity\FileManager\File;
use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Entity\PrintAttribution\TrackedFile;
use VisageFour\Bundle\ToolsBundle\Message\FileManager\DeleteFile;
use VisageFour\Bundle\ToolsBundle\Message\GenerateGraphicalComposite;
use VisageFour\Bundle\ToolsBundle\Traits\LoggerTrait;
use VisageFour\Bundle\ToolsBundle\Services\Message\LoggedMessageBus;

/**
 * Class MessagePrep
 * @package App\VisageFour\Bundle\ToolsBundle\Services\Message
 *
 * each method dispatches a cmd-msg, but it also does useful stuff like:
 * - logging of ids (and other useful information)
 * - setting of flags (e.g. status to "TO_BE_DELETED")
 *
 * You should use this instead of MessageBus directly
 */
class MessageDispatcher
{
    use LoggerTrait;

    /**
     * @var LoggedMessageBus
     */
    private $messageBus;
    /**
     * @var EntityManager
     */
    private $em;

    // useful in setting a standard format for the 'pre-dispatch' log
    private $msgPrefix;

    public function __construct(EntityManager $em, LoggedMessageBus $messageBus)
    {
        $this->messageBus = $messageBus;
        $this->em = $em;

        $this->msgPrefix = '(Preparing cmd-msg dispatch of:)';
    }

    /**
     * @param TrackedFile $trackedFile
     * @return bool
     * @throws \Exception
     *
     * Create a cmd-msg to:
     * generate a composite image/pdf via: GenerateGraphicalCompositeHandler
     */
    public function dispatchGenerateGraphicalComposite(TrackedFile $trackedFile): Bool
    {
        if ($trackedFile->getStatus() == TrackedFile::STATUS_GENERATED) {
            throw new \Exception('TrackedFile (with id: '. $trackedFile->getId() .') has already been generated. It does not need to be generated again.');
        }

        $trackedFile->setStatus(TrackedFile::STATUS_IN_QUEUE);

        // provide extra context to the message about to be sent (such as id and any other useful information)
        $msgSuffix = '(Preparing cmd-msg dispatch of: )';
        $msg = $this->msgPrefix. 'Generate composite file ('. $trackedFile->getShortName().' id: '. $trackedFile->getId() .')';
        $this->logger->info($msg, $trackedFile, 'cyan');

        $message = new GenerateGraphicalComposite($trackedFile);
        $this->messageBus->dispatch($message);

        $this->em->flush();

        return true;
    }

    /**
     * @param TrackedFile $trackedFile
     * @return bool
     * @throws \Exception
     *
     * Create a cmd-msg to:
     * delete a file on local storage, remote storage and mark the File db record as deleted.
     */
    public function dispatchDeleteFile(File $file): Bool
    {
        $file->setStatus(File::STATUS_MARKED_FOR_DELETION);

        // provide extra context to the message about to be sent (such as id and any other useful information)
        $msg = $this->msgPrefix. 'deletion of File entity (id: '. $file->getId() .', originalBasename: '. $file->getOriginalBasename() .')';
        $this->logger->info($msg, $file, 'cyan');

        $message = new DeleteFile($file);
        $this->messageBus->dispatch($message);

        $this->em->flush();

        return true;
    }


}