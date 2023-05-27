<?php

namespace App\Repositories\Team;

use App\Models\Member;

class MemberRepository
{
    public function saveMemberInvitation($result)
    {
        foreach($result['invited_id'] as $invitedId) {
            Member::create([
                'team_id' => $result['team_id'],
                'invited_by_id' => $result['invited_by_id'],
                'invited_id' => $invitedId,
                'status' => $result['status'],
                'invitation_sent_at' => now()
            ]);
        }

        return true;
    }
}
