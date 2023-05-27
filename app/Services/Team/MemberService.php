<?php

namespace App\Services\Team;

use App\Repositories\Team\MemberRepository;

class MemberService
{
    public function saveMemberInvitation($result)
    {
        $member = new MemberRepository;
        return $member->saveMemberInvitation($result);
    }
}
