<?php

namespace KoninklijkeCollective\MyUserManagement\Controller;

use KoninklijkeCollective\MyUserManagement\Domain\Model\BackendUser;
use KoninklijkeCollective\MyUserManagement\Domain\Repository\BackendUserGroupRepository;
use KoninklijkeCollective\MyUserManagement\Domain\Repository\BackendUserRepository;
use KoninklijkeCollective\MyUserManagement\Functions\TranslateTrait;
use KoninklijkeCollective\MyUserManagement\Utility\AccessUtility;
use TYPO3\CMS\Beuser\Domain\Model\Demand;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserSessionRepository;
use TYPO3\CMS\Beuser\Service\ModuleDataStorageService;
use TYPO3\CMS\Beuser\Service\UserInformationService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Override Controller: BackendUser
 *
 * @see \TYPO3\CMS\Beuser\Controller\BackendUserController
 */
final class BackendUserController extends \TYPO3\CMS\Beuser\Controller\BackendUserController
{
    use TranslateTrait;

    /**
     * Override generic backend user repository
     *
     * @var \KoninklijkeCollective\MyUserManagement\Domain\Repository\BackendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $backendUserRepository;

    /**
     * Override generic backend user group repository
     *
     * @var \KoninklijkeCollective\MyUserManagement\Domain\Repository\BackendUserGroupRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $backendUserGroupRepository;

    /**
     * @param  \TYPO3\CMS\Beuser\Service\ModuleDataStorageService  $moduleDataStorageService
     * @param  \KoninklijkeCollective\MyUserManagement\Domain\Repository\BackendUserRepository  $backendUserRepository
     * @param  \KoninklijkeCollective\MyUserManagement\Domain\Repository\BackendUserGroupRepository  $backendUserGroupRepository
     * @param  \TYPO3\CMS\Beuser\Domain\Repository\BackendUserSessionRepository  $backendUserSessionRepository
     * @param  \TYPO3\CMS\Beuser\Service\UserInformationService  $userInformationService
     */
    public function __construct(
        ModuleDataStorageService $moduleDataStorageService,
        BackendUserRepository $backendUserRepository,
        BackendUserGroupRepository $backendUserGroupRepository,
        BackendUserSessionRepository $backendUserSessionRepository,
        UserInformationService $userInformationService
    ) {
        parent::__construct($moduleDataStorageService, $backendUserRepository, $backendUserGroupRepository, $backendUserSessionRepository, $userInformationService);
    }

    /**
     * @param  \TYPO3\CMS\Extbase\Mvc\View\ViewInterface  $view
     * @return void
     */
    protected function initializeView(ViewInterface $view): void
    {
        parent::initializeView($view);
        // Override shortcut label
        $view->assign('shortcutLabel', 'myBackendUsers');
    }

    /**
     * Displays all BackendUsers
     * - Switch session to different user
     *
     * @param  \TYPO3\CMS\Beuser\Domain\Model\Demand  $demand
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function indexAction(?Demand $demand = null): void
    {
        if (AccessUtility::beUserHasRightToEditTable(BackendUser::TABLE) === false) {
            $this->addFlashMessage(
                self::translate('backend_user_no_rights_to_table_description', [BackendUser::TABLE]),
                self::translate('backend_user_no_rights_to_table_title'),
                AbstractMessage::ERROR
            );
        }

        if (!$this->getBackendUserAuthentication()->isAdmin()) {
            if ($demand === null) {
                $demand = $this->moduleData->getDemand();
            } elseif ($demand->getUserType() !== Demand::USERTYPE_USERONLY) {
                $this->addFlashMessage(
                    self::translate('filter_on_admin_is_not_allowed_description'),
                    self::translate('filter_on_admin_is_not_allowed_title'),
                    AbstractMessage::ERROR
                );
            }

            $demand->setUserType(Demand::USERTYPE_USERONLY);
            $this->moduleData->setDemand($demand);
        }

        parent::indexAction($demand);

        // Override backend user group key for view
        $this->view->assign(
            'backendUserGroups',
            array_merge([''], $this->backendUserGroupRepository->findAllConfigured()->toArray())
        );
    }

    /**
     * @param  int  $switchUser
     * @return void
     * @inheritDoc
     */
    public function switchUser($switchUser): void
    {
        if ($this->getBackendUserAuthentication()->isAdmin()) {
            parent::switchUser($switchUser);
        }
        /** @var \KoninklijkeCollective\MyUserManagement\Domain\Model\BackendUser|null $targetUser */
        $targetUser = $this->backendUserRepository->findByUid($switchUser);
        if ($targetUser === null) {
            $this->addFlashMessage(
                self::translate('switch_user_not_found_description'),
                self::translate('switch_user_not_found_title'),
                AbstractMessage::ERROR
            );

            return;
        }

        if ($targetUser->getIsAdministrator()) {
            $this->addFlashMessage(
                self::translate('admin_switch_not_allowed_description'),
                self::translate('admin_switch_not_allowed_title'),
                AbstractMessage::ERROR
            );

            return;
        }

        // If all is as expected, fake admin functionality before switching user
        $this->getBackendUserAuthentication()->user['admin'] = 1;
        parent::switchUser($switchUser);
    }
}
